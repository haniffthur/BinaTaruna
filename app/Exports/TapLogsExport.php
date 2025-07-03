<?php

namespace App\Exports;

use App\Models\TapLog;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TapLogsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Defines the query to fetch data from the database based on filters.
     */
    public function query()
    {
        // Use an instance of TaplogsController to call the buildQuery method
        // This is the best way to avoid duplicating complex query code.
        $controller = new \App\Http\Controllers\TaplogsController();
        
        // Since buildQuery is private, we need to make it accessible temporarily
        // using Reflection. This is an advanced trick to keep the code clean (DRY).
        $reflection = new \ReflectionMethod(\App\Http\Controllers\TaplogsController::class, 'buildQuery');
        $reflection->setAccessible(true);

        return $reflection->invoke($controller, $this->request)->latest('tapped_at');
    }

    /**
     * Defines the headers for each column in the Excel file.
     */
    public function headings(): array
    {
        return [
            'Waktu Tap',
            'Nomor Kartu',
            'Nama Pemilik',
            'Tipe',
            'Detail (Kelas/Posisi/Spesialisasi)',
            'Status',
            'Pesan Sistem',
        ];
    }

    /**
     * Maps each row of data to the desired format.
     * @param mixed $log The log data from the query.
     */
    public function map($log): array
    {
        $ownerName = $log->member_name ?? $log->coach_name ?? $log->staff_name ?? 'Kartu Tidak Terhubung';
        if(!$log->master_card_id) {
            $ownerName = 'Kartu Telah Dihapus';
        }

        $ownerType = 'Tidak Diketahui';
        $cardDetail = '-';
        if($log->member_name) { $ownerType = 'Member'; $cardDetail = $log->class_name ?? 'Tanpa Kelas'; }
        elseif($log->coach_name) { $ownerType = 'Pelatih'; $cardDetail = $log->coach_specialization ?? '-'; }
        elseif($log->staff_name) { $ownerType = 'Staff'; $cardDetail = $log->staff_position ?? '-'; }
        
        // --- PERBAIKAN: Terjemahkan status 1/0 menjadi Granted/Denied ---
        $statusText = 'Tidak Diketahui';
        if ($log->status == 1 || $log->status === 'granted') {
            $statusText = 'Granted';
        } elseif ($log->status == 0 || $log->status === 'denied') {
            $statusText = 'Denied';
        }
        // --- AKHIR DARI PERBAIKAN ---

        return [
            Carbon::parse($log->tapped_at)->format('Y-m-d H:i:s'),
            $log->cardno ?? $log->card_uid,
            $ownerName,
            $ownerType,
            $cardDetail,
            $statusText, // Gunakan variabel yang sudah diterjemahkan
            $log->message,
        ];
    }
}
