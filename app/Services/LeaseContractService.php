<?php

namespace App\Services;

class LeaseContractService
{
    /**
     * Tính toán tổng tiền thuê sàn kèm thuế VAT và phí dịch vụ quản lý
     */
    public function calculateMonthlyRent(float $areaSqm, float $pricePerSqm, float $serviceFeePerSqm, float $vatRate = 0.1): array
    {
        $baseRent = $areaSqm * $pricePerSqm;
        $serviceFee = $areaSqm * $serviceFeePerSqm;
        $vat = ($baseRent + $serviceFee) * $vatRate;
        $total = $baseRent + $serviceFee + $vat;

        return [
            'base_rent'   => $baseRent,
            'service_fee' => $serviceFee,
            'vat'         => $vat,
            'total'       => $total,
        ];
    }
}