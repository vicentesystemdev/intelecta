<?php

namespace Database\Seeders;

use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Tema;
use Illuminate\Database\Seeder;

class PreguntasSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->questions() as $item) {
            $tema = Tema::where('nombre_tem', $item['tema'])->firstOrFail();
            $pregunta = Pregunta::updateOrCreate(
                ['enunciado_preg' => $item['enunciado']],
                [
                    'id_tem' => $tema->id_tem,
                    'tipo_preg' => 'opcion_multiple',
                    'dificultad_preg' => $item['dificultad'],
                    'puntaje_preg' => 1,
                    'explicacion_preg' => $item['explicacion'],
                    'estado_preg' => 'activo',
                ],
            );

            $pregunta->alternativas()->delete();

            foreach ($item['alternativas'] as $index => $texto) {
                $pregunta->alternativas()->create([
                    'texto_alt' => $texto,
                    'letra_alt' => chr(65 + $index),
                    'es_correcta_alt' => $index === $item['correcta'],
                    'orden_alt' => $index + 1,
                    'estado_alt' => 'activo',
                ]);
            }
        }
    }

    private function questions(): array
    {
        return [
            $this->q('Porcentajes', 'Una academia cuenta con 250 postulantes y el 18 % requiere reforzamiento en aritmética. ¿Cuántos postulantes integran ese grupo?', 'basica', 'Se calcula 0,18 × 250 = 45.', ['35', '40', '45', '50', '55'], 2),
            $this->q('Razones y proporciones', 'Dos grupos guardan la razón 3:5 y reúnen 64 postulantes. ¿Cuántos pertenecen al grupo menor?', 'media', 'Las ocho partes equivalen a 64; cada parte vale 8 y el grupo menor tiene 24.', ['18', '21', '24', '32', '40'], 2),
            $this->q('Regla de tres simple y compuesta', 'Seis auxiliares organizan material académico en 15 días. Manteniendo el mismo ritmo, ¿en cuántos días lo harán diez auxiliares?', 'media', 'El trabajo es constante: 6 × 15 = 10 × d; por tanto d = 9.', ['6', '8', '9', '10', '12'], 2),
            $this->q('Fracciones', 'El valor de 3/4 + 5/6 es:', 'basica', 'El mínimo común múltiplo es 12: 9/12 + 10/12 = 19/12.', ['17/12', '18/12', '19/12', '20/12', '21/12'], 2),
            $this->q('Problemas financieros básicos', 'Un capital de Bs 4.000 genera interés simple anual del 6 %. ¿Qué interés produce en ocho meses?', 'media', 'I = 4000 × 0,06 × 8/12 = Bs 160.', ['Bs 120', 'Bs 140', 'Bs 160', 'Bs 180', 'Bs 240'], 2),

            $this->q('Factorización', 'La factorización completa de x² - 5x + 6 es:', 'media', 'Se buscan dos números cuyo producto sea 6 y cuya suma sea -5: -2 y -3.', ['(x + 2)(x + 3)', '(x - 1)(x - 6)', '(x - 2)(x - 3)', '(x + 1)(x - 6)', 'No es factorizable'], 2),
            $this->q('Productos notables', 'Al desarrollar (2x - 3)² se obtiene:', 'basica', 'Se aplica (a-b)² = a² - 2ab + b².', ['4x² - 9', '4x² - 6x + 9', '4x² - 12x + 9', '2x² - 12x + 9', '4x² + 12x + 9'], 2),
            $this->q('Ecuaciones lineales', 'Si 3(2x - 1) = 4x + 7, el valor de x es:', 'basica', '6x - 3 = 4x + 7; entonces 2x = 10 y x = 5.', ['2', '3', '4', '5', '6'], 3),
            $this->q('Ecuaciones de segundo grado', 'Las soluciones de x² - 7x + 12 = 0 son:', 'media', 'La ecuación se factoriza como (x-3)(x-4)=0.', ['1 y 12', '2 y 6', '3 y 4', '-3 y -4', 'No tiene solución real'], 2),
            $this->q('Sistemas de ecuaciones', 'En el sistema 2x + y = 11 y x - y = 1, el par ordenado (x,y) es:', 'media', 'Al sumar las ecuaciones resulta 3x = 12; luego x = 4 y y = 3.', ['(3,4)', '(4,3)', '(5,1)', '(2,7)', '(6,-1)'], 1),

            $this->q('Teorema de Pitágoras', 'Un triángulo rectángulo tiene catetos de 9 cm y 12 cm. ¿Cuál es la longitud de la hipotenusa?', 'basica', 'c² = 9² + 12² = 225; por tanto c = 15.', ['13 cm', '14 cm', '15 cm', '18 cm', '21 cm'], 2),
            $this->q('Áreas de figuras planas', 'Un trapecio tiene bases de 10 m y 16 m, y altura de 7 m. Su área es:', 'media', 'A = (B+b)h/2 = 26 × 7 / 2 = 91.', ['78 m²', '84 m²', '91 m²', '96 m²', '112 m²'], 2),
            $this->q('Razones trigonométricas', 'Para un ángulo agudo θ se conoce sen θ = 3/5. ¿Cuál es cos θ?', 'media', 'Por la identidad sen²θ + cos²θ = 1, cos θ = 4/5.', ['2/5', '3/4', '4/5', '5/4', '1'], 2),
            $this->q('Perímetros', 'Un rectángulo tiene perímetro de 54 cm y largo de 16 cm. ¿Cuál es su área?', 'media', '2(16+a)=54, de donde a=11; el área es 16×11=176.', ['160 cm²', '168 cm²', '176 cm²', '184 cm²', '192 cm²'], 2),
            $this->q('Triángulos rectángulos', 'Un triángulo rectángulo tiene lados 7 cm, 24 cm y 25 cm. ¿Cuál es su área?', 'media', 'Los catetos son 7 y 24; A = 7 × 24 / 2 = 84.', ['72 cm²', '80 cm²', '84 cm²', '96 cm²', '100 cm²'], 2),

            $this->q('Sucesiones numéricas', 'Determine el siguiente término: 2, 6, 12, 20, 30, ...', 'media', 'Los términos siguen n(n+1); después de 5×6 corresponde 6×7.', ['36', '40', '42', '44', '48'], 2),
            $this->q('Sucesiones numéricas', 'Determine el siguiente término: 3, 8, 18, 38, ...', 'avanzada', 'Cada término se obtiene duplicando el anterior y sumando 2.', ['68', '72', '76', '78', '80'], 3),
            $this->q('Operadores no convencionales', 'Se define a ★ b = 2a + b. El valor de 7 ★ 4 es:', 'basica', 'Al sustituir: 2(7)+4=18.', ['14', '16', '18', '20', '22'], 2),
            $this->q('Problemas de edades', 'La edad de una madre es el triple de la edad de su hija y entre ambas suman 48 años. ¿Qué edad tiene la hija?', 'media', 'Si la hija tiene x, la madre tiene 3x; 4x=48 y x=12.', ['10 años', '11 años', '12 años', '14 años', '16 años'], 2),
            $this->q('Analogías lógico-matemáticas', 'Complete la analogía según la relación n → n(n+1): 4 es a 20 como 6 es a:', 'media', 'Para n=6 se obtiene 6×7=42.', ['30', '36', '40', '42', '48'], 3),

            $this->q('Promedio aritmético', 'El promedio de 12, 15, 18, 20 y 25 es:', 'basica', 'La suma es 90 y se divide entre 5: 18.', ['16', '17', '18', '19', '20'], 2),
            $this->q('Mediana y moda', 'La mediana del conjunto 5, 7, 7, 9, 14, 18 es:', 'media', 'Al haber seis datos, se promedian el tercero y el cuarto: (7+9)/2=8.', ['7', '7,5', '8', '8,5', '9'], 2),
            $this->q('Mediana y moda', 'En el conjunto 4, 6, 6, 6, 8, 9, 9, la moda es:', 'basica', 'El valor 6 aparece tres veces, más que cualquier otro.', ['4', '6', '8', '9', 'No existe'], 1),
            $this->q('Probabilidad básica', 'Una urna contiene 3 fichas rojas, 2 azules y 5 verdes. La probabilidad de extraer una ficha roja es:', 'basica', 'Hay 3 resultados favorables entre 10 fichas: 3/10.', ['1/5', '1/4', '3/10', '1/3', '1/2'], 2),
            $this->q('Interpretación de tablas', 'En un registro de 20 postulantes, 4 obtuvieron 60 puntos, 6 obtuvieron 70, 8 obtuvieron 80 y 2 obtuvieron 90. ¿Qué porcentaje alcanzó al menos 80 puntos?', 'media', 'Diez de veinte alcanzaron 80 o más: 10/20 = 50 %.', ['40 %', '45 %', '50 %', '55 %', '60 %'], 2),

            $this->q('Problemas contextualizados', 'Un minibús recorre 180 km utilizando 15 litros de combustible. Si mantiene el rendimiento, ¿cuántos litros requiere para 300 km?', 'media', 'La razón es 12 km por litro; 300/12=25 litros.', ['20', '22', '24', '25', '27'], 3),
            $this->q('Comparación de magnitudes', 'En la mañana la temperatura fue 4 °C y durante la noche descendió 11 °C. ¿Cuál fue la temperatura nocturna?', 'basica', 'Se calcula 4 - 11 = -7.', ['-15 °C', '-7 °C', '-5 °C', '7 °C', '15 °C'], 1),
            $this->q('Modelado con ecuaciones', 'Una entrada académica y dos cuadernos cuestan Bs 46. Si cada cuaderno cuesta Bs 13, ¿cuál es el precio de la entrada?', 'basica', 'Si x es la entrada: x + 2(13)=46; x=20.', ['Bs 16', 'Bs 18', 'Bs 20', 'Bs 22', 'Bs 24'], 2),
            $this->q('Interpretación de enunciados', 'Un postulante responde correctamente 36 de 45 ejercicios. ¿Qué fracción simplificada representa sus respuestas correctas?', 'media', '36/45 se simplifica dividiendo numerador y denominador entre 9: 4/5.', ['2/3', '3/4', '4/5', '5/6', '8/9'], 2),
            $this->q('Modelado con ecuaciones', 'Dos números consecutivos suman 73. ¿Cuál es el mayor?', 'media', 'Si son n y n+1, entonces 2n+1=73; n=36 y el mayor es 37.', ['35', '36', '37', '38', '39'], 2),
        ];
    }

    private function q(
        string $tema,
        string $enunciado,
        string $dificultad,
        string $explicacion,
        array $alternativas,
        int $correcta,
    ): array {
        return compact('tema', 'enunciado', 'dificultad', 'explicacion', 'alternativas', 'correcta');
    }
}
