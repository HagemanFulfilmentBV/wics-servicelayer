<?php

namespace Hageman\Wics\ServiceLayer\Factories;

use DateInterval;
use DateTime;
use Hageman\Wics\ServiceLayer\Requests\Items;

trait OrderFactory
{    
    private static function lines(Items $items, int $maxLines): array
    {             
        $lines = [];
        for($i = 0; $i < min($maxLines, $items->count()); $i++) {
            $quantity = rand(1, 5);
            $vatPercentage = ([6, 9, 21])[rand(0, 2)];
            $unitPriceNet = rand(2, 20);
            $unitPriceGross = $unitPriceNet * (1 + ($vatPercentage / 100));
            $discount = rand(0, 2);
            
            $lines[] = [
                'itemCode' => $items->get("$i.code"),
                'itemDescription' => $items->get("$i.description"),
                'quantity' => $quantity,
                'prices' => [
                    'vatPercentage' => $vatPercentage,
                    'unitPriceNet' => $unitPriceNet,
                    'unitPriceGross' => $unitPriceGross,
                    'priceNet' => $unitPriceNet * $quantity,
                    'priceGross' => $unitPriceGross * $quantity,
                    'discount' => $discount
                ]
            ];
        }
        
        return $lines;
    }
    
    public static function fake(Items $items, int $maxLines = 20, DateTime $deliveryDate = null): array
    {
        if(is_null($deliveryDate)) $deliveryDate = (new DateTime('now'))->add(DateInterval::createFromDateString('1 week'));
        
        $lines = collect(self::lines($items, min(1, $maxLines)));
        
        return [
            'reference' => 'ORD_T_' . date('YmdHis'),
            'additionalReference' => '',
            'deliveryDate' => $deliveryDate->format('Y-m-d'),
            'paid' => true,
            'webshopId' => 2,
            'note' => '',
            'prices' => [
                'currency' => 'EUR',
                'totalPriceNet' => $lines->sum(function($line) {
                    return data_get($line, 'prices.priceNet');
                }),
                'totalPriceGross' => $lines->sum(function($line) {
                    return data_get($line, 'prices.priceGross');
                }),
                'totalDiscount' => $lines->sum(function($line) {
                    return data_get($line, 'prices.discount');
                }),
                'handlingCost' => rand(0, 3),
                'shippingCost' => rand(1, 9),
                'rembours' => rand(0, 1)
            ],
            'texts' => [
                'picking' => 'Pick oldest item first!',
                'scanner' => 'Hello mr picker holding the scanner',
                'packingTable' => 'Check for undamaged package',
                'packingSlip' => 'Print packing slip',
                'shippingLabel' => 'Double check shipment'
            ],
            'invoiceAddress' => [
                'name' => 'Invoice to',
                'nameExtension' => '',
                'company' => 'Invoice company',
                'street' => 'TestStreet',
                'streetNumber' => 1,
                'extension' => 'a',
                'secondAddressLine' => 'TestRegion',
                'thirdAddressLine' => 'Testing Block',
                'zipcode' => '1234 AB',
                'city' => 'TestCity',
                'state' => 'Province',
                'country' => 'NL',
                'phoneNumber' => '0123456789',
                'mobileNumber' => '0612345678',
                'contactPerson' => 'ATT',
                'email' => 'someone@example.com',
                'language' => 'NL'
            ],
            'deliveryAddress' => [
                'name' => 'Deliver to',
                'nameExtension' => '',
                'company' => 'Deliver company',
                'street' => 'TestStreet',
                'streetNumber' => 1,
                'extension' => 'a',
                'secondAddressLine' => 'TestRegion',
                'thirdAddressLine' => 'Testing Block',
                'zipcode' => '1234 AB',
                'city' => 'TestCity',
                'state' => 'Province',
                'country' => 'NL',
                'phoneNumber' => '0123456789',
                'mobileNumber' => '0612345678',
                'contactPerson' => 'ATT',
                'email' => 'someone@example.com',
                'language' => 'NL'
            ],
            'lines' => $lines->toArray(),
        ];
    }
}