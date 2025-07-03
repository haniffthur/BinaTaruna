<?php

namespace App\Exports;

use App\Models\Member;
use Maatwebsite\Excel\Concerns\FromCollection; // Untuk ekspor dari Collection
use Maatwebsite\Excel\Concerns\WithHeadings;   // Untuk menyertakan header
use Maatwebsite\Excel\Concerns\WithMapping;    // Untuk memformat data setiap baris
use Illuminate\Http\Request; // Untuk mendapatkan parameter filter
use Carbon\Carbon; // Untuk format tanggal

class MembersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $members = Member::with(['schoolClass', 'accessRule'])->select(
            'id',
            'name',
            'nickname',
            'nis',
            'nisnas',
            'address',
            'phone_number',
            'date_of_birth',
            'parent_name',
            'join_date',
            'school_class_id', // Akan di-map ke nama kelas
            'master_card_id',  // Akan di-map ke UID kartu
            'access_rule_id',  // Akan di-map ke nama aturan
            'rule_type',
            'max_taps_per_day',
            'max_taps_per_month',
            'allowed_days',
            'start_time',
            'end_time',
            'created_at',
            'updated_at'
            // Pastikan semua kolom yang ingin Anda ekspor ada di sini
        );

        // Terapkan filter yang diterima dari controller
        if (!empty($this->filters['name'])) {
            $members->where('name', 'like', '%' . $this->filters['name'] . '%');
        }

        if (!empty($this->filters['school_class_id'])) {
            $members->where('school_class_id', $this->filters['school_class_id']);
        }

        if (!empty($this->filters['join_date'])) {
            $members->whereDate('join_date', $this->filters['join_date']);
        }

        return $members->get(); // Mengambil data setelah filter diterapkan
    }

    /**
     * Mengembalikan baris header untuk Excel.
     * Ini adalah nama-nama kolom yang akan muncul di baris pertama Excel.
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nama Lengkap',
            'Nama Panggilan',
            'NIS',
            'NISNAS',
            'Alamat',
            'Nomor Telepon',
            'Tanggal Lahir',
            'Nama Orang Tua',
            'Tanggal Bergabung',
            'Kelas',
            'UID Kartu RFID',
            'Aturan Akses (Template ID)',
            'Tipe Aturan Akses',
            'Maks. Tap Per Hari',
            'Maks. Tap Per Bulan',
            'Hari Diizinkan (JSON)',
            'Jam Mulai',
            'Jam Selesai',
            'Tanggal Dibuat',
            'Tanggal Diperbarui'
        ];
    }

    /**
     * Memetakan data dari setiap baris menjadi format yang siap untuk Excel.
     * Ini berguna untuk mengambil nama relasi (kelas, aturan akses) daripada ID-nya.
     * @param mixed $member
     * @return array
     */
    public function map($member): array
    {
        return [
            $member->id,
            $member->name,
            $member->nickname,
            $member->nis,
            $member->nisnas,
            $member->address,
            $member->phone_number,
            ($member->date_of_birth instanceof Carbon) ? $member->date_of_birth->format('Y-m-d') : $member->date_of_birth,
            $member->parent_name,
            ($member->join_date instanceof Carbon) ? $member->join_date->format('Y-m-d') : $member->join_date,
            $member->schoolClass->name ?? '-', // Mengambil nama kelas
            $member->masterCard->cardno ?? '-', // Mengambil UID kartu
            $member->accessRule->name ?? '-', // Mengambil nama aturan akses
            $member->rule_type,
            $member->max_taps_per_day,
            $member->max_taps_per_month,
            is_array($member->allowed_days) ? json_encode($member->allowed_days) : $member->allowed_days,
            ($member->start_time instanceof Carbon) ? $member->start_time->format('H:i:s') : $member->start_time,
            ($member->end_time instanceof Carbon) ? $member->end_time->format('H:i:s') : $member->end_time,
            ($member->created_at instanceof Carbon) ? $member->created_at->format('Y-m-d H:i:s') : $member->created_at,
            ($member->updated_at instanceof Carbon) ? $member->updated_at->format('Y-m-d H:i:s') : $member->updated_at,
        ];
    }
}