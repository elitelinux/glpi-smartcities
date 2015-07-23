<?php

/**
 * Permite la creación de imágenes de degradados mediante la API GD
 *
 * @author Javier Marín
 */
class PluginCustomGradientgd {
    /**
     * Genera un gradiente
     * Basada en http://planetozh.com/blog/my-projects/images-php-gd-gradient-fill/
     * @param int $width Ancho, en píxeles, del gradiente generado
     * @param int $height Alto, en píxeles, del gradiente generado
     * @param array $colors Array asociativo donde la clave es el porcentaje y el valor el color (en formato hex o array rgb)
     * @param string $direction Dirección del gradiente (vertical, horizontal, diagonal, ellipse, square, diamond)
     * @param bool $invert Invertir colores del degradado
     * @return mixed
     */
    public function generate_gradient($width, $height, $colors, $direction='vertical', $invert=false) {
        //Crear imagen
        $image = imagecreatetruecolor($width, $height);
        //Comprobar colores
        $positions = array_keys($colors);
        if (!isset($colors[0]))//Usar el primer color
            $colors[0] = $colors[reset($positions)];
        if (!isset($colors[100]))
            $colors[1000] = $colors[end($positions)];
        //Calcular el número de líneas a dibujar
        $lines;
        switch ($direction) {
            case 'vertical':
                $lines = $height;
                break;
            case 'horizontal':
                $lines = $width;
                break;
            case 'diagonal':
                $lines = max($width, $height) * 2;
                break;
            case 'ellipse':
                $center_x = $width / 2;
                $center_y = $height / 2;
                $rh = $height > $width ? 1 : $width / $height;
                $rw = $width > $height ? 1 : $height / $width;
                $lines = min($width, $height);
                //Rellenar fondo
                list($r1, $g1, $b1) = $this->_hex2rgb($colors[100]);
                imagefill($image, 0, 0, imagecolorallocate($image, $r1, $g1, $b1));
                $invert = !$invert; //Es necesario para no tener que dibujar el degradado del revés
                break;
            case 'square':
            case 'rectangle':
                $lines = max($width, $height) / 2;
                $invert = !$invert; //Es necesario para no tener que dibujar el degradado del revés
                break;
            case 'diamond':
                $rh = $height > $width ? 1 : $width / $height;
                $rw = $width > $height ? 1 : $height / $width;
                $lines = min($width, $height);
                $invert = !$invert; //Es necesario para no tener que dibujar el degradado del revés
                break;
        }
        //Invertir colores
        if ($invert) {
            $invert_colors = array();
            foreach ($colors as $key => $value) {
                $invert_colors[100 - $key] = $value;
            }
            $colors = $invert_colors;
        }
        ksort($colors);
        //Dibujar línea a línea
        $incr = 1;
        $color_change_positions = array_keys($colors);
        $end_color_progress = 0; //Forzar que en la primera iteración se seleccione el rango de colores
        for ($i = 0; $i < $lines; $i = $i + $incr) {
            //Escoger color
            $total_progress = 100 / $lines * $i;
            if ($total_progress >= $end_color_progress) {//Cambiar de rango de colores
                //Buscar color inicial a partir del progreso total
                $j = intval($total_progress);
                do {
                    $color_index = array_search($j--, $color_change_positions);
                } while ($color_index === false && $j >= 0);
                //Obtener colores inicio y final para este rango
                $start_color_progress = $color_change_positions[$color_index];
                $start_color = $this->_hex2rgb($colors[$start_color_progress]);
                $end_color_progress = $color_change_positions[$color_index + 1];
                $end_color = $this->_hex2rgb($colors[$end_color_progress]);
            }
            $internal_progress = ($total_progress - $start_color_progress) / ($end_color_progress - $start_color_progress);
            $r = $start_color[0] + ($end_color[0] - $start_color[0]) * $internal_progress;
            $g = $start_color[1] + ($end_color[1] - $start_color[1]) * $internal_progress;
            $b = $start_color[2] + ($end_color[2] - $start_color[2]) * $internal_progress;
            $color = imagecolorallocate($image, $r, $g, $b);
            //Dibujar línea
            switch ($direction) {
                case 'vertical':
                    imagefilledrectangle($image, 0, $i, $width, $i + $incr, $color);
                    break;
                case 'horizontal':
                    imagefilledrectangle($image, $i, 0, $i + $incr, $height, $color);
                    break;
                case 'diagonal':
                    imagefilledpolygon($image, array(
                        $i, 0,
                        $i + $incr, 0,
                        0, $i + $incr,
                        0, $i), 4, $color);
                    break;
                case 'ellipse':
                    imagefilledellipse($image, $center_x, $center_y, ($lines - $i) * $rh, ($lines - $i) * $rw, $color);
                    break;
                case 'square':
                case 'rectangle':
                    imagefilledrectangle($image, $i * $width / $height, $i * $height / $width, $width - ($i * $width / $height), $height - ($i * $height / $width), $color);
                    break;
                case 'diamond':
                    imagefilledpolygon($image, array(
                        $width / 2, $i * $rw - 0.5 * $height,
                        $i * $rh - 0.5 * $width, $height / 2,
                        $width / 2, 1.5 * $height - $i * $rw,
                        1.5 * $width - $i * $rh, $height / 2), 4, $color);
                    break;
            }
        }
        return $image;
    }
    public function save_image($image, $path, $format='png', $quality=100) {
        switch ($format) {
            case 'jpg':
            case 'jpeg':
                return imagejpeg($image, $path, $quality);
            case 'gif':
                return imagegif($image, $path);
            default:
                imagesavealpha($image, true);
                return imagepng($image, $path, 9, PNG_ALL_FILTERS); //Compresión máxima
        }
    }
    /**
     * Convierte un color en formato html hexadecimal a formato array RGB
     * @param string $color Color en formato #ffffff o #fff
     * @return array
     */
    private function _hex2rgb($color) {
        if (is_array($color))
            return $color;
        $color = str_replace('#', '', $color);
        $s = strlen($color) / 3;
        $rgb[] = hexdec(str_repeat(substr($color, 0, $s), 2 / $s));
        $rgb[] = hexdec(str_repeat(substr($color, $s, $s), 2 / $s));
        $rgb[] = hexdec(str_repeat(substr($color, 2 * $s, $s), 2 / $s));
        return $rgb;
    }
}
