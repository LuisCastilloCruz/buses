<?php

namespace App\CoreFacturalo\Services\Extras;

use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ValidateCpeSunat
{
    const URL_CONSULT = 'https://api.sunat.gob.pe/v1/contribuyente/contribuyentes/20601411076/validarcomprobante';
    const URL_TOKEN = 'https://api-seguridad.sunat.gob.pe/v1/clientesextranet/11d21fcf-2a30-4e98-bd5b-fb56f1e9096f/oauth2/token/';

    protected $client;

    protected $document_state = [
        '-' => '-',
        '0' => 'NO EXISTE',
        '1' => 'ACEPTADO',
        '2' => 'ANULADO',
        '3' => 'AUTORIZADO',
        '4' => 'NO AUTORIZADO'
    ];

    protected $company_state = [
        '-' => '-',
        '00' => 'ACTIVO',
        '01' => 'BAJA PROVISIONAL',
        '02' => 'BAJA PROV. POR OFICIO',
        '03' => 'SUSPENSION TEMPORAL',
        '10' => 'BAJA DEFINITIVA',
        '11' => 'BAJA DE OFICIO',
        '12' => 'BAJA MULT.INSCR. Y OTROS ',
        '20' => 'NUM. INTERNO IDENTIF.',
        '21' => 'OTROS OBLIGADOS',
        '22' => 'INHABILITADO-VENT.UNICA',
        '30' => 'ANULACION - ERROR SUNAT   '
    ];

    protected $company_condition = [
        '-' => '-',
        '00' => 'HABIDO',
        '01' => 'NO HALLADO SE MUDO DE DOMICILIO',
        '02' => 'NO HALLADO FALLECIO',
        '03' => 'NO HALLADO NO EXISTE DOMICILIO',
        '04' => 'NO HALLADO CERRADO',
        '05' => 'NO HALLADO NRO.PUERTA NO EXISTE',
        '06' => 'NO HALLADO DESTINATARIO DESCONOCIDO',
        '07' => 'NO HALLADO RECHAZADO',
        '08' => 'NO HALLADO OTROS MOTIVOS',
        '09' => 'PENDIENTE',
        '10' => 'NO APLICABLE',
        '11' => 'POR VERIFICAR',
        '12' => 'NO HABIDO',
        '20' => 'NO HALLADO',
        '21' => 'NO EXISTE LA DIRECCION DECLARADA',
        '22' => 'DOMICILIO CERRADO',
        '23' => 'NEGATIVA RECEPCION X PERSONA CAPAZ',
        '24' => 'AUSENCIA DE PERSONA CAPAZ',
        '25' => 'NO APLICABLE X TRAMITE DE REVERSION',
        '40' => 'DEVUELTO'
    ];

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => self::URL_CONSULT,
            'defaults' => [
                'exceptions' => false,
                'allow_redirects' => false
            ]
        ]);
        $this->client = new Client(['cookies' => true]);
    }

    public function search($company_number, $document_type_id, $series, $number, $date_of_issue, $total,$token)
    {
        try {
            //$token = trim($this->getToken());
            $response = $this->client->request('POST', self::URL_CONSULT, [
                'http_errors' => true,
                'headers' => [
                    'Accept' =>'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization'=> 'Bearer '.$token
                ],
                'json' =>[
                    "numRuc" => $company_number,
                    "codComp" => $document_type_id,
                    "numeroSerie" => $series,
                    "numero" => $number,
                    "fechaEmision" => $date_of_issue,
                    "monto" =>$total
                ]
            ]);

            //dd($response->getBody()->getContents());
            if($response->getStatusCode() == 200) {
                $text =  $response->getBody()->getContents();
                $datos = json_decode($text,true);
                return [
                    'success' => true,
                    'response' => "Ok ".$response->getBody()->getContents(),
                    'data' => [
                        'comprobante_estado_codigo' => $datos['data']['estadoCp'],
                        'comprobante_estado_descripcion' => $this->document_state[$datos['data']['estadoCp']],
                        // 'empresa_estado_codigo' => $response->data->estadoRuc,
                        // 'empresa_estado_description' => $this->company_state[$response->data->estadoRuc],
                        // 'empresa_condicion_codigo' => $response->data->condDomiRuc,
                        // 'empresa_condicion_descripcion' => $this->company_condition[$response->data->condDomiRuc],
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Error ".$response->getBody()->getContents()
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function getToken()
    {

        $response = $this->client->request('POST', self::URL_TOKEN, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Cookie'=> 'TS019e7fc2=014dc399cb1f1388e01d6630c18c6465440051f1c724bd2d8e5cd962e9c8410958bbbe63aec493f97ce17780f02a6698bc42ca60ed',
                'Content-Length'=> 178
                ],
            'json' =>[
                'grant_type'=>'client_credentials',
                'scope'=>'https://api.sunat.gob.pe/v1/contribuyente/contribuyentes',
                'client_id'=>'11d21fcf-2a30-4e98-bd5b-fb56f1e9096f',
                'client_secret'=>'OhQ25/Gh55x8CFwsal1FAg==',
            ],
        ]);
        return $response;
        $text =  $response->getBody()->getContents();
        $datos = json_decode($text,true);


            // dd($acceso['access_token']);

        return $datos['access_token'];
    }

}
