<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeRecordReportCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        $paginator = $this->resource;
        
        return [
            'data' => $paginator->getCollection()->transform(function ($timeRecord) {
                return new TimeRecordReportResource($timeRecord);
            }),
            'summary' => $this->getSummary(),
            'meta' => [
                'total' => $paginator->total(),
                'count' => $paginator->count(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'total_pages' => $paginator->lastPage(),
                'has_more_pages' => $paginator->hasMorePages(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ];
    }

    /**
     * Get summary statistics
     */
    private function getSummary(): array
    {
        $records = $this->resource->getCollection();
        
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
