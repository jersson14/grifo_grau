<?php
require_once dirname(__DIR__, 2) . '/config/env.php';

/** El servicio no respondió: no se sabe si el documento se registró. Hay que consultar antes de reenviar. */
class Error_Red_Facturacion extends RuntimeException
{
}

/**
 * Cliente de la API JSON de Nubefact. Formato tomado de sus ejemplos oficiales (NubeFact-json.php y
 * EJEMPLOS-DE-ARCHIVOS-JSON). Toda la dependencia del proveedor está aquí: cambiar de proveedor
 * significa escribir otra clase con estos mismos métodos.
 */
class Proveedor_Nubefact
{
    public function __construct(private string $ruta, private string $token)
    {
    }

    public function Generar(array $documento): array
    {
        return $this->Enviar(['operacion' => 'generar_comprobante'] + $documento);
    }

    public function Consultar(int $tipo, string $serie, int $numero): array
    {
        return $this->Enviar(['operacion' => 'consultar_comprobante', 'tipo_de_comprobante' => (string) $tipo, 'serie' => $serie, 'numero' => (string) $numero]);
    }

    public function Anular(int $tipo, string $serie, int $numero, string $motivo): array
    {
        return $this->Enviar(['operacion' => 'generar_anulacion', 'tipo_de_comprobante' => (string) $tipo, 'serie' => $serie, 'numero' => (string) $numero, 'motivo' => $motivo, 'codigo_unico' => '']);
    }

    public function Consultar_Anulacion(int $tipo, string $serie, int $numero): array
    {
        return $this->Enviar(['operacion' => 'consultar_anulacion', 'tipo_de_comprobante' => (string) $tipo, 'serie' => $serie, 'numero' => (string) $numero]);
    }

    /** Devuelve la respuesta decodificada, con la clave 'errors' si Nubefact rechazó la operación. */
    private function Enviar(array $datos): array
    {
        $curl = curl_init($this->ruta);
        curl_setopt_array($curl, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($datos, JSON_UNESCAPED_UNICODE),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 45,
            CURLOPT_HTTPHEADER     => ['Authorization: Token token="' . $this->token . '"', 'Content-Type: application/json'],
        ]);
        $cuerpo = curl_exec($curl);
        if ($cuerpo === false) {
            throw new Error_Red_Facturacion('Sin conexión con Nubefact: ' . curl_error($curl));
        }
        $codigo = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $respuesta = json_decode((string) $cuerpo, true);
        if ($codigo >= 500 || !is_array($respuesta)) {
            throw new Error_Red_Facturacion("Nubefact respondió de forma inesperada (HTTP $codigo).");
        }
        return $respuesta;
    }
}
