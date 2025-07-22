<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TimeRecordReportCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->transform(function ($timeRecord) {
                return new TimeRecordReportResource($timeRecord);
            }),
            'summary' => $this->getSummary(),
            'meta' => [
                'total' => $this->total(),
                'count' => $this->count(),
                'per_page' => $this->perPage(),
                'current_page' => $this->currentPage(),
                'total_pages' => $this->lastPage(),
                'has_more_pages' => $this->hasMorePages(),
            ],
            'links' => [
                'first' => $this->url(1),
                'last' => $this->url($this->lastPage()),
                'prev' => $this->previousPageUrl(),
                'next' => $this->nextPageUrl(),
            ],
        ];
    }

    /**
     * Get summary statistics
     */
    private function getSummary(): array
    {
        $records = $this->collection;
        
        return [
            'total_records' => $records->count(),
            'unique_employees' => $records->unique('user_id')->count(),
            'date_range' => [
                'start' => $records->min('recorded_at')?->format('d/m/Y'),
                'end' => $records->max('recorded_at')?->format('d/m/Y'),
            ],
            'periods' => [
                'morning' => $records->filter(function ($record) {
                    $hour = (int) $record->recorded_at->format('H');
                    return $hour >= 5 && $hour < 12;
                })->count(),
                'afternoon' => $records->filter(function ($record) {
                    $hour = (int) $record->recorded_at->format('H');
                    return $hour >= 12 && $hour < 18;
                })->count(),
                'evening' => $records->filter(function ($record) {
                    $hour = (int) $record->recorded_at->format('H');
                    return $hour >= 18 && $hour < 24;
                })->count(),
                'dawn' => $records->filter(function ($record) {
                    $hour = (int) $record->recorded_at->format('H');
                    return $hour >= 0 && $hour < 5;
                })->count(),
            ],
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return [
            'message' => 'Relatório de registros de ponto gerado com sucesso',
            'status' => 'success',
        ];
    }
}
