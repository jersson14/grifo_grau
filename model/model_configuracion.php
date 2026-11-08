<?php
require_once 'model_conexion.php';

class Modelo_Configuracion extends conexionBD
{
    /** Claves editables desde la interfaz. Lo que no esté aquí no se puede guardar. */
    public const CLAVES = [
        'igv_porcentaje'         => ['tipo' => 'numero', 'min' => 0, 'max' => 100],
        'credito_dias'           => ['tipo' => 'numero', 'min' => 0, 'max' => 365],
        'moneda_simbolo'         => ['tipo' => 'texto'],
        'turno_tolerancia_soles' => ['tipo' => 'numero', 'min' => 0, 'max' => 10000],
        'respaldo_conservar'     => ['tipo' => 'numero', 'min' => 1, 'max' => 365],
        'varillaje_tolerancia_pct' => ['tipo' => 'numero', 'min' => 0, 'max' => 100],
        'clave_forzar_cambio'    => ['tipo' => 'opcion', 'opciones' => ['1', '0']],
        'ia_modelo'              => ['tipo' => 'opcion', 'opciones' => ['claude-opus-5-5', 'claude-sonnet-5-5', 'claude-haiku-5-5']],
        'ia_esfuerzo'            => ['tipo' => 'opcion', 'opciones' => ['low', 'medium', 'high']],
        'ia_limite_mensual_usd'  => ['tipo' => 'numero', 'min' => 0, 'max' => 100000],
        'ia_roles'               => ['tipo' => 'roles'],
        'inicio_alertas'         => ['tipo' => 'lista'],
        'inicio_bloques'         => ['tipo' => 'bloques'],
        'factura_ruta'           => ['tipo' => 'patron', 'opcional' => true, 'patron' => '#^https://([a-z0-9-]+\.)*nubefact\.com/api/v1/[A-Za-z0-9-]+$#'],
        'factura_token'          => ['tipo' => 'secreto'],
        'factura_serie_factura'  => ['tipo' => 'patron', 'patron' => '/^F[A-Z0-9]{3}$/'],
        'factura_serie_boleta'   => ['tipo' => 'patron', 'patron' => '/^B[A-Z0-9]{3}$/'],
        'factura_unidad_medida'  => ['tipo' => 'patron', 'patron' => '/^[A-Z0-9]{2,3}$/'],
        'factura_boleta_tope_sin_documento' => ['tipo' => 'numero', 'min' => 0, 'max' => 100000],
        'ia_api_key'             => ['tipo' => 'secreto'],
        'decolecta_token'        => ['tipo' => 'secreto'],
    ];

    /** Valores para el formulario. Los secretos solo indican si están configurados. */
    public function Listar_Para_Formulario(): array
    {
        $c = conexionBD::conexionPDO();
        $valores = [];
        foreach ($c->query('SELECT clave, valor, secreto FROM configuracion')->fetchAll(PDO::FETCH_ASSOC) as $fila) {
            if ($fila['secreto']) {
                $valores[$fila['clave'] . '_configurado'] = $fila['valor'] !== '' && $fila['valor'] !== null;
            } else {
                $valores[$fila['clave']] = $fila['valor'];
            }
        }
        return $valores;
    }

    public function Obtener(string $clave, ?string $porDefecto = null): ?string
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare('SELECT valor, secreto FROM configuracion WHERE clave = ?');
        $query->execute([$clave]);
        $fila = $query->fetch(PDO::FETCH_ASSOC);
        if (!$fila || $fila['valor'] === null || $fila['valor'] === '') {
            return $porDefecto;
        }
        return $fila['secreto'] ? self::Descifrar($fila['valor']) : $fila['valor'];
    }

    /** Guarda los valores válidos. Un secreto en blanco conserva el valor anterior. */
    public function Guardar(array $datos, int $idUsuario): array
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare('INSERT INTO configuracion (clave, valor, secreto, actualizado_en, actualizado_por) VALUES (?, ?, ?, NOW(), ?)
                              ON DUPLICATE KEY UPDATE valor = VALUES(valor), secreto = VALUES(secreto), actualizado_en = NOW(), actualizado_por = VALUES(actualizado_por)');
        $errores = [];
        $cambios = [];

        foreach ($datos as $clave => $valor) {
            $regla = self::CLAVES[$clave] ?? null;
            if ($regla === null || !is_string($valor)) {
                continue;
            }
            $valor = trim($valor);

            switch ($regla['tipo']) {
                case 'numero':
                    if (!is_numeric($valor) || $valor < $regla['min'] || $valor > $regla['max']) {
                        $errores[] = $clave;
                        continue 2;
                    }
                    break;
                case 'opcion':
                    if (!in_array($valor, $regla['opciones'], true)) {
                        $errores[] = $clave;
                        continue 2;
                    }
                    break;
                case 'patron':
                    if ($valor === '' && !empty($regla['opcional'])) {
                        break;
                    }
                    if (!preg_match($regla['patron'], $valor)) {
                        $errores[] = $clave;
                        continue 2;
                    }
                    break;
                case 'roles':
                    require_once __DIR__ . '/model_roles.php';
                    $roles = array_intersect(array_map('trim', explode(',', $valor)), Modelo_Roles::Codigos());
                    $valor = implode(',', $roles);
                    break;
                case 'lista':
                    require_once __DIR__ . '/model_alertas.php';
                    $valor = implode(',', array_intersect(explode(',', $valor), array_keys(Modelo_Alertas::ALERTAS)));
                    break;
                case 'bloques':
                    require_once __DIR__ . '/model_inicio.php';
                    $valor = implode(',', array_intersect(explode(',', $valor), array_keys(Modelo_Inicio::BLOQUES)));
                    break;
                case 'secreto':
                    if ($valor === '') {
                        continue 2;
                    }
                    $valor = self::Cifrar($valor);
                    break;
                default:
                    $valor = mb_substr($valor, 0, 255);
            }

            $query->execute([$clave, $valor, $regla['tipo'] === 'secreto' ? 1 : 0, $idUsuario]);
            $cambios[] = $clave;
        }

        if ($cambios) {
            $this->Auditar($c, $idUsuario, 'CONFIGURACION', implode(', ', $cambios));
        }
        return ['success' => !$errores, 'errores' => $errores];
    }

    public function Listar_Modulos(): array
    {
        $c = conexionBD::conexionPDO();
        return $c->query('SELECT codigo, nombre, descripcion, activo FROM modulos ORDER BY nombre')->fetchAll(PDO::FETCH_ASSOC);
    }

    public function Cambiar_Modulo(string $codigo, bool $activo, int $idUsuario): bool
    {
        $c = conexionBD::conexionPDO();
        $query = $c->prepare('UPDATE modulos SET activo = ?, actualizado_en = NOW() WHERE codigo = ?');
        $query->execute([$activo ? 1 : 0, $codigo]);
        $this->Auditar($c, $idUsuario, $activo ? 'MODULO_ACTIVADO' : 'MODULO_DESACTIVADO', $codigo);
        return $query->rowCount() > 0;
    }

    public static function Modulo_Activo(string $codigo): bool
    {
        $c = conexionBD::conexionPDO();
        if ($c === null) {
            return false;
        }
        $query = $c->prepare('SELECT activo FROM modulos WHERE codigo = ?');
        $query->execute([$codigo]);
        return (bool) $query->fetchColumn();
    }

    private function Auditar(PDO $c, int $idUsuario, string $accion, string $detalle): void
    {
        $query = $c->prepare('INSERT INTO auditoria (id_usuario, usuario, accion, detalle, ip) VALUES (?, ?, ?, ?, ?)');
        $query->execute([$idUsuario, $_SESSION['S_USU'] ?? null, $accion, $detalle, $_SERVER['REMOTE_ADDR'] ?? null]);
    }

    private static function Llave(): string
    {
        $hex = env('APP_KEY', '');
        if (!is_string($hex) || strlen($hex) !== 64 || !ctype_xdigit($hex)) {
            throw new RuntimeException('APP_KEY no está configurada en .env');
        }
        return hex2bin($hex);
    }

    private static function Cifrar(string $texto): string
    {
        $iv = random_bytes(12);
        $etiqueta = '';
        $cifrado = openssl_encrypt($texto, 'aes-256-gcm', self::Llave(), OPENSSL_RAW_DATA, $iv, $etiqueta);
        return base64_encode($iv . $etiqueta . $cifrado);
    }

    private static function Descifrar(string $guardado): ?string
    {
        $crudo = base64_decode($guardado, true);
        if ($crudo === false || strlen($crudo) < 28) {
            return null;
        }
        $texto = openssl_decrypt(substr($crudo, 28), 'aes-256-gcm', self::Llave(), OPENSSL_RAW_DATA, substr($crudo, 0, 12), substr($crudo, 12, 16));
        return $texto === false ? null : $texto;
    }
}
