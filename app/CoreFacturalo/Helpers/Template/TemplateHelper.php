<?php

    namespace App\CoreFacturalo\Helpers\Template;


    use App\Models\Tenant\SaleNote;
    use App\Models\Tenant\Dispatch;
    use App\Models\Tenant\Document;
    use App\Models\Tenant\DocumentFee;
    use App\Models\Tenant\DocumentPayment;
    use App\Models\Tenant\PaymentCondition;

    class TemplateHelper
    {


        /**
         * Devuelve la condicion de pago para un Document.
         * Las condiciones son Credito o Contado.
         *
         * @param Document|SaleNote $document
         *
         * @return string|null
         * @example
         *          <?php
         *          $condition =
         *          \App\CoreFacturalo\Helpers\Template\TemplateHelper::getDocumentPaymentCondition($document);
         *          ?>
         *          {{ $condition  }}
         *
         */
        public static function getDocumentPaymentCondition( $document)
        {
            // Condicion de pago  Crédito / Contado
            if ($document) {
                if ($document->payment_condition) {
                    return $document->payment_condition->name;
                }
                if(get_class($document)==SaleNote::class){
                    // Las notas de venta no tiene condición de pago.
                    if($document->payment_method_type) {
                        return $document->payment_method_type->description;
                    }
                    $payments = $document->payments;
                    if($document->payment_method_type_id && $payments->count() == 0) {
                        return $document->payment_method_type->description;
                    }

                }
            }
            return '-';
            /** @var   PaymentCondition $paymentCondition */
            $paymentCondition = ($document->payment_condition_id === '01') ?
                PaymentCondition::where('id', '10')->first() :
                PaymentCondition::where('id', '09')->first();

            return $paymentCondition->name;
        }


        /**
         * Devuelve un array con los detalles de pago.
         *
         * @param Document|SaleNote $document
         *
         * @return array
         */
        public static function getDetailedPayment( $document, $dateFormat = 'Y-m-d')
        {
            $data = [];
            $payments = $document->payments;
            if ($document->payment_condition_id === '01') {
                $data['PAGOS'] = [];
                /** @var DocumentPayment $row */
                foreach ($payments as $row) {
                    $temp = [
                        'description' => $row->payment_method_type->description,
                        'reference' => $row->reference ? $row->reference . ' - ' : '',
                        'symbol' => $document->currency_type->symbol,
                        'amount' => $row->payment + $row->change,
                    ];

                    $data['PAGOS'][] = $temp;
                }
            } else {
                if(!empty($document->fee)) {
                    $data['CUOTA'] = [];
                    /**
                     * @var int          $key
                     * @var  DocumentFee $quote
                     */
                    foreach ($document->fee as $key => $quote) {
                        $temp = [
                            'description' => (empty($quote->getStringPaymentMethodType()) ? 'Cuota #' . ($key + 1) : $quote->getStringPaymentMethodType()),
                            'reference' => $quote->date->format($dateFormat),
                            'amount' => $quote->amount,
                            'symbol' => $quote->symbol,
                        ];
                        $data['CUOTA'][] = $temp;

                    }
                }

            }
            if(get_class($document)==SaleNote::class && $payments->count()!= 0){
                // Las notas de venta no tiene condicion de pago.

                /** @var \App\Models\Tenant\SaleNotePayment $row */
                foreach ($payments as $row) {
                    $temp = [
                        'date_of_payment' => $row->date_of_payment->format($dateFormat),
                        'description' => $row->payment_method_type->description,
                        'reference' => $row->reference ? $row->reference . ' - ' : '',
                        'symbol' => $document->currency_type->symbol,
                        'payment' => $row->payment,
                        'amount' => $row->payment + $row->change,
                    ];

                    $data['PAGOS'][] = $temp;
                    // $payment += (float) $row->payment;
                }
            }


            return $data;
        }

        /**
         * Devuelve las guias de un documento, Primero las guias que esten escritas y luego las guias relacionadas
         *
         * @param Document $document
         *
         * @return array
         * @example
         *         <?php
         * @php
         *     $guias = \App\CoreFacturalo\Helpers\Template\TemplateHelper::getGuides($document);
         * @endphp
         * @if(!empty($guias))
         *     <td class="font-sm" width="100px">
         *     <strong>Guía de Remisión</strong>
         *     </td>
         *     <td class="font-sm" width="8px">:</td>
         *     <td class="font-sm" colspan="4">
         * @foreach ($guias as $guides)
         * @foreach($guides as $index => $item)
         *     {{ $item }}<br>
         * @endforeach
         * @endforeach
         *     </td>
         * @endif
         *     ?>
         */
        public static function getGuides(Document $document)
        {
            $data = [];

            if ($document->guides != null) {
                foreach ($document->guides as $guide) {
                    $type = '';
                    if (isset($guide->document_type_description)) {
                        $type = $guide->document_type_description;
                    } else {
                        if ($guide->document_type_id) {
                            $type = $guide->document_type_id;
                        }
                    }
                    if ( !isset($data[$type])) $data[$type] = [];
                    $data[$type][] = $guide->document_type_description . ": " . $guide->number;
                }
            }

            $type = 'model';
            if ($document->dispatch) {
                /** @var Dispatch $dispatch */
                $dispatch = $document->dispatch;
                if ( !isset($data[$type])) $data[$type] = [];
                $data[$type][] = $dispatch->series . "-" . $dispatch->number;


            }
            return $data;
        }

        /**
         * Devuelve un string html para salto de linea
         *
         * @return string
         */
        public static function breakLine(): string
        {

            return '<div style="page-break-after: always;"></div>';
        }

        public static function setNumber($number, $decimal = 2, $mil = ',', $dec = '.')
        {
            return number_format($number, $decimal, $mil, $dec);
        }
    }
