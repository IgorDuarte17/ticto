<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'cep' => $this->formatCep($this->resource['cep'] ?? ''),
            'address' => $this->resource['address'] ?? '',
            'neighborhood' => $this->resource['neighborhood'] ?? '',
            'city' => $this->resource['city'] ?? '',
            'state' => $this->resource['state'] ?? '',
            'complement' => $this->resource['complement'] ?? '',
            'full_address' => $this->getFullAddress(),
        ];
    }

    private function formatCep(?string $cep): ?string
    {
        if (!$cep) {
            return $cep;
        }

        $cep = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cep) !== 8) {
            return $cep;
        }

        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }

    private function getFullAddress(): string
    {
        $parts = array_filter([
            $this->resource['address'] ?? '',
            $this->resource['neighborhood'] ?? '',
            $this->resource['city'] ?? '',
            $this->resource['state'] ?? '',
        ]);

        $address = implode(', ', $parts);
        
        if ($this->resource['cep'] ?? '') {
            $address .= ' - CEP: ' . $this->formatCep($this->resource['cep']);
        }

        return $address;
    }
}
