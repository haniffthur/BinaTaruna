<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\MasterCard;
use App\Models\AccessRule;
use App\Models\SchoolClass;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Shared\Date; 
// use PhpOffice\PhpSpreadsheet\Reader\IReader; // Tidak digunakan, bisa dihapus

class MembersImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    private $errors = [];

    public function model(array $row)
    {
        // Debugging sementara: dd($row) di sini akan menunjukkan isi setiap baris yang dibaca
        // dd($row);

        // --- PENYESUAIAN LOGIKA UTAMA DI SINI ---
        // Periksa apakah kunci-kunci penting ada dan tidak kosong.
        // Ini akan menangani baris kosong atau baris yang tidak memiliki header yang dipetakan.
        if (
            !array_key_exists('nama_lengkap', $row) || empty($row['nama_lengkap']) ||
            !array_key_exists('tanggal_bergabung', $row) || empty($row['tanggal_bergabung'])
        ) {
            // Jika nama_lengkap atau tanggal_bergabung tidak ada kuncinya atau kosong,
            // baris ini kemungkinan besar adalah baris kosong yang tidak diinginkan.
            // Kita lewati baris ini.
            return null;
        }
        // --- AKHIR PENYESUAIAN LOGIKA UTAMA ---


        // Mencari atau membuat MasterCard
        $masterCard = null;
        if (!empty($row['kartu_rfid_uid'])) {
            $masterCard = MasterCard::firstWhere('cardno', $row['kartu_rfid_uid']);
            if ($masterCard && $masterCard->assignment_status === 'assigned') {
                $this->errors[] = 'Kartu RFID "' . $row['kartu_rfid_uid'] . '" sudah terpakai. Baris ini dilewati.';
                return null;
            } elseif ($masterCard) {
                $masterCard->update(['assignment_status' => 'assigned']);
            } else {
                $masterCard = MasterCard::create([
                    'cardno' => $row['kartu_rfid_uid'], 'card_type' => 'member',
                    'assignment_status' => 'assigned', 'is_active' => true,
                ]);
            }
        }

        // Mencari AccessRule berdasarkan nama
        $accessRule = null;
        if (!empty($row['nama_aturan_akses'])) {
            $accessRule = AccessRule::firstWhere('name', $row['nama_aturan_akses']);
            if (!$accessRule) {
                $this->errors[] = 'Aturan akses template "' . $row['nama_aturan_akses'] . '" tidak ditemukan. Baris ini dilewati.';
                return null;
            }
        }

        // Mencari SchoolClass berdasarkan nama
        $schoolClass = null;
        if (!empty($row['nama_kelas'])) {
            $schoolClass = SchoolClass::firstWhere('name', $row['nama_kelas']);
            if (!$schoolClass) {
                $this->errors[] = 'Kelas "' . $row['nama_kelas'] . '" tidak ditemukan. Baris ini dilewati.';
                return null;
            }
        }

        // Tanggal lahir dan tanggal bergabung
        $dateOfBirth = null;
        if (!empty($row['tanggal_lahir'])) {
            if (is_numeric($row['tanggal_lahir'])) {
                try {
                    $dateOfBirth = Date::excelToDateTimeObject($row['tanggal_lahir']);
                } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
                    $dateOfBirth = Carbon::parse($row['tanggal_lahir']);
                }
            } else {
                $dateOfBirth = Carbon::parse($row['tanggal_lahir']);
            }
        }

        $joinDate = null;
        // Kita sudah jamin 'tanggal_bergabung' tidak kosong di awal method
        if (is_numeric($row['tanggal_bergabung'])) {
            try {
                $joinDate = Date::excelToDateTimeObject($row['tanggal_bergabung']);
            } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
                $joinDate = Carbon::parse($row['tanggal_bergabung']);
            }
        } else {
            $joinDate = Carbon::parse($row['tanggal_bergabung']);
        }

        $memberData = [
            'name' => $row['nama_lengkap'],
            'nickname' => $row['nama_panggilan'] ?? null,
            'nis' => $row['nis'] ?? null,
            'nisnas' => $row['nisnas'] ?? null,
            'address' => $row['alamat'] ?? null,
            'phone_number' => $row['nomor_telepon'] ?? null,
            'date_of_birth' => $dateOfBirth ?? null ,
            'parent_name' => $row['nama_orang_tua'] ?? null,
            'join_date' => $joinDate, // join_date sekarang dijamin tidak null karena pemeriksaan di awal
            'school_class_id' => $schoolClass->id ?? null,
            'master_card_id' => $masterCard->id ?? null,
            'access_rule_id' => $accessRule->id ?? null,
            'rule_type' => 'template', // Tidak perlu ?? null karena sudah default
            'max_taps_per_day' => null, 'max_taps_per_month' => null,
            'allowed_days' => null, 'start_time' => null, 'end_time' => null,
            'daily_tap_reset_at' => now(), // now() selalu mengembalikan objek Carbon, tidak perlu ?? null
            'monthly_tap_reset_at' => now(), // now() selalu mengembalikan objek Carbon, tidak perlu ?? null
        ];

        return new Member($memberData);
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'nullable|string|max:255', // Sudah nullable
            'nama_panggilan' => 'nullable|string|max:255',
            'nis' => ['nullable', 'numeric', Rule::unique('members', 'nis')->whereNull('deleted_at')],
            'nisnas' => ['nullable', 'numeric', Rule::unique('members', 'nisnas')->whereNull('deleted_at')],
            'alamat' => 'nullable|string|max:255',
            'nomor_telepon' => 'nullable|numeric',
            'tanggal_lahir' => 'nullable|date',
            'nama_orang_tua' => 'nullable|string|max:255',
            'tanggal_bergabung' => 'nullable|date', // Sudah nullable, tapi DB-nya NOT NULL
            'kartu_rfid_uid' => ['nullable', 'string', 'max:255'],
            'nama_kelas' => 'nullable|string|exists:classes,name', // Perhatikan, tabelnya 'school_classes' bukan 'classes'
            'nama_aturan_akses' => 'nullable|string|exists:access_rules,name',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nis.unique' => 'NIS ini sudah terdaftar untuk member lain.',
            'nisnas.unique' => 'NISNAS ini sudah terdaftar untuk member lain.',
            // Perhatikan: Pesan ini tidak konsisten dengan rule 'nullable' di atas.
            // Sebaiknya dihapus jika 'tanggal_bergabung' tidak wajib.
            // Atau jika memang wajib diisi untuk data valid, kembali ke 'required' di rules().
            'tanggal_bergabung.required' => 'Tanggal bergabung wajib diisi.',
            'nama_kelas.exists' => 'Kelas yang dimasukkan tidak ditemukan.',
            'nama_aturan_akses.exists' => 'Aturan akses template yang dimasukkan tidak ditemukan.',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errors[] = 'Baris ' . $failure->row() . ': ' . implode(', ', $failure->errors());
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}