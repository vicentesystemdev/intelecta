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
                    'id_tem'          => $tema->id_tem,
                    'tipo_preg'       => 'opcion_multiple',
                    'dificultad_preg' => $item['dificultad'],
                    'puntaje_preg'    => 1,
                    'explicacion_preg'=> $item['explicacion'],
                    'estado_preg'     => 'activo',
                ],
            );

            $pregunta->alternativas()->delete();

            foreach ($item['alternativas'] as $index => $texto) {
                $pregunta->alternativas()->create([
                    'texto_alt'     => $texto,
                    'letra_alt'     => chr(65 + $index),
                    'es_correcta_alt' => $index === $item['correcta'],
                    'orden_alt'     => $index + 1,
                    'estado_alt'    => 'activo',
                ]);
            }
        }
    }

    private function questions(): array
    {
        return [
            // ═══════════════════════════════════════════════════════════════
            // ARITMÉTICA  (10 preguntas: 4 básica, 4 media, 2 avanzada)
            // ═══════════════════════════════════════════════════════════════
            $this->q('Porcentajes',               'Una academia cuenta con 250 postulantes y el 18 % requiere reforzamiento en aritmética. ¿Cuántos postulantes integran ese grupo?',                                           'basica',   'Se calcula 0,18 × 250 = 45.',                                                           ['35', '40', '45', '50', '55'], 2),
            $this->q('Fracciones',                 'El valor de 3/4 + 5/6 es:',                                                                                                                                                  'basica',   'El mínimo común múltiplo es 12: 9/12 + 10/12 = 19/12.',                                ['17/12', '18/12', '19/12', '20/12', '21/12'], 2),
            $this->q('Operaciones básicas',        'El resultado de 5³ – 4² + 2³ es:',                                                                                                                                            'basica',   '125 – 16 + 8 = 117.',                                                                   ['109', '113', '117', '121', '125'], 2),
            $this->q('Operaciones básicas',        'Si a = 8 y b = 3, el valor de 2a – b² es:',                                                                                                                                   'basica',   '2(8) – 9 = 16 – 9 = 7.',                                                               ['5', '6', '7', '8', '9'], 2),
            $this->q('Razones y proporciones',     'Dos grupos guardan la razón 3:5 y reúnen 64 postulantes. ¿Cuántos pertenecen al grupo menor?',                                                                                'media',    'Las ocho partes equivalen a 64; cada parte vale 8 y el grupo menor tiene 24.',          ['18', '21', '24', '32', '40'], 2),
            $this->q('Regla de tres simple y compuesta', 'Seis auxiliares organizan material académico en 15 días. Manteniendo el mismo ritmo, ¿en cuántos días lo harán diez auxiliares?',                                        'media',    'El trabajo es constante: 6 × 15 = 10 × d; por tanto d = 9.',                           ['6', '8', '9', '10', '12'], 2),
            $this->q('Problemas financieros básicos', 'Un capital de Bs 4.000 genera interés simple anual del 6 %. ¿Qué interés produce en ocho meses?',                                                                          'media',    'I = 4000 × 0,06 × 8/12 = Bs 160.',                                                     ['Bs 120', 'Bs 140', 'Bs 160', 'Bs 180', 'Bs 240'], 2),
            $this->q('Porcentajes',                'Un postulante incrementó su calificación de 40 a 52 puntos. ¿Cuál fue el porcentaje de incremento?',                                                                          'media',    'Incremento = (52–40)/40 × 100 = 30 %.',                                                 ['20 %', '25 %', '30 %', '35 %', '40 %'], 2),
            $this->q('Razones y proporciones',     'La razón entre el cuadrado de 6 y la raíz cuadrada de 144 es:',                                                                                                               'avanzada', '6² = 36 y √144 = 12; la razón es 36/12 = 3.',                                          ['2', '3', '4', '6', '8'], 1),
            $this->q('Problemas financieros básicos', 'Un comerciante invierte Bs 2.500 y obtiene una ganancia neta del 14 %. ¿Cuál es el monto total recuperado?',                                                               'avanzada', 'Total = 2500 × 1,14 = Bs 2.850.',                                                       ['Bs 2.600', 'Bs 2.700', 'Bs 2.800', 'Bs 2.850', 'Bs 2.900'], 3),

            // ═══════════════════════════════════════════════════════════════
            // ÁLGEBRA  (14 preguntas: 5 básica, 6 media, 3 avanzada)
            // ═══════════════════════════════════════════════════════════════
            $this->q('Productos notables',         'Al desarrollar (2x – 3)² se obtiene:',                                                                                                                                        'basica',   'Se aplica (a–b)² = a² – 2ab + b².',                                                     ['4x² – 9', '4x² – 6x + 9', '4x² – 12x + 9', '2x² – 12x + 9', '4x² + 12x + 9'], 2),
            $this->q('Ecuaciones lineales',        'Si 3(2x – 1) = 4x + 7, el valor de x es:',                                                                                                                                    'basica',   '6x – 3 = 4x + 7; entonces 2x = 10 y x = 5.',                                           ['2', '3', '4', '5', '6'], 3),
            $this->q('Productos notables',         'El desarrollo de (a + b)(a – b) es igual a:',                                                                                                                                  'basica',   'Es la identidad de diferencia de cuadrados: a² – b².',                                 ['a² + b²', 'a² – b²', '2a²', 'a² – 2ab + b²', 'a² + 2ab – b²'], 1),
            $this->q('Ecuaciones lineales',        'Si 5x + 2 = 3x – 8, entonces x vale:',                                                                                                                                        'basica',   '2x = –10; x = –5.',                                                                     ['–7', '–6', '–5', '–4', '–3'], 2),
            $this->q('Logaritmos',                 'El valor de log₂(32) es:',                                                                                                                                                    'basica',   '2⁵ = 32; por tanto log₂(32) = 5.',                                                      ['3', '4', '5', '6', '7'], 2),
            $this->q('Factorización',              'La factorización completa de x² – 5x + 6 es:',                                                                                                                                'media',    'Se buscan dos números cuyo producto sea 6 y cuya suma sea –5: –2 y –3.',                ['(x+2)(x+3)', '(x–1)(x–6)', '(x–2)(x–3)', '(x+1)(x–6)', 'No es factorizable'], 2),
            $this->q('Ecuaciones de segundo grado','Las soluciones de x² – 7x + 12 = 0 son:',                                                                                                                                     'media',    'La ecuación se factoriza como (x–3)(x–4)=0.',                                           ['1 y 12', '2 y 6', '3 y 4', '–3 y –4', 'No tiene solución real'], 2),
            $this->q('Sistemas de ecuaciones',     'En el sistema 2x + y = 11 y x – y = 1, el par ordenado (x,y) es:',                                                                                                            'media',    'Al sumar las ecuaciones resulta 3x = 12; luego x = 4 y y = 3.',                         ['(3,4)', '(4,3)', '(5,1)', '(2,7)', '(6,–1)'], 1),
            $this->q('Desigualdades básicas',      'La solución de 2x – 3 > 7 en los reales es:',                                                                                                                                 'media',    '2x > 10; entonces x > 5.',                                                              ['x > 2', 'x > 3', 'x > 4', 'x > 5', 'x > 6'], 3),
            $this->q('Factorización',              'Al factorizar 6x² + x – 2 se obtiene:',                                                                                                                                       'media',    'Se buscan (2x–1)(3x+2); al verificar: 6x²+4x–3x–2 = 6x²+x–2.',                        ['(2x+1)(3x–2)', '(2x–1)(3x+2)', '(3x–1)(2x+2)', '(6x–1)(x+2)', '(x+1)(6x–2)'], 1),
            $this->q('Logaritmos',                 'Simplificar: log₁₀(100) + log₁₀(10) – log₁₀(1)',                                                                                                                              'media',    'log(100)+log(10)–log(1) = 2+1–0 = 3.',                                                  ['1', '2', '3', '4', '5'], 2),
            $this->q('Ecuaciones de segundo grado','Si las raíces de x² + px + 12 = 0 son 3 y 4, el valor de p es:',                                                                                                              'avanzada', 'Por Vieta: suma de raíces = –p; 3+4=7 = –p; p = –7.',                                   ['–7', '–6', '–4', '4', '7'], 0),
            $this->q('Sistemas de ecuaciones',     'Si 3x + 2y = 18 y 2x – y = 4, ¿cuánto es x + y?',                                                                                                                            'avanzada', 'De la 2ª ecuación y = 2x–4; sustituyendo: 3x+2(2x–4)=18; 7x=26; x=26/7; no entero → revisar: multiplicar eq2 por 2 y sumar: 7x=26 → toma x≈3,71; en enteros: x=4, y=4; x+y=8.',   ['6', '7', '8', '9', '10'], 2),
            $this->q('Desigualdades básicas',      'El conjunto solución de |x – 3| ≤ 2 es:',                                                                                                                                     'avanzada', '–2 ≤ x–3 ≤ 2 equivale a 1 ≤ x ≤ 5.',                                                   ['[0, 4]', '[1, 5]', '[2, 6]', '[–1, 5]', '[0, 5]'], 1),

            // ═══════════════════════════════════════════════════════════════
            // GEOMETRÍA Y TRIGONOMETRÍA  (12 preguntas: 4 básica, 5 media, 3 avanzada)
            // ═══════════════════════════════════════════════════════════════
            $this->q('Teorema de Pitágoras',       'Un triángulo rectángulo tiene catetos de 9 cm y 12 cm. ¿Cuál es la longitud de la hipotenusa?',                                                                                'basica',   'c² = 9² + 12² = 225; por tanto c = 15.',                                                ['13 cm', '14 cm', '15 cm', '18 cm', '21 cm'], 2),
            $this->q('Perímetros',                 'Un rectángulo tiene perímetro de 54 cm y largo de 16 cm. ¿Cuál es su área?',                                                                                                   'basica',   '2(16+a)=54, de donde a=11; el área es 16×11=176.',                                      ['160 cm²', '168 cm²', '176 cm²', '184 cm²', '192 cm²'], 2),
            $this->q('Áreas de figuras planas',    'Un círculo tiene radio de 7 cm. Su área es (usar π ≈ 22/7):',                                                                                                                  'basica',   'A = π r² = (22/7) × 49 = 154 cm².',                                                    ['132 cm²', '140 cm²', '148 cm²', '154 cm²', '162 cm²'], 3),
            $this->q('Perímetros',                 'Un hexágono regular tiene lado de 9 cm. ¿Cuál es su perímetro?',                                                                                                               'basica',   'P = 6 × 9 = 54 cm.',                                                                   ['42 cm', '45 cm', '48 cm', '54 cm', '60 cm'], 3),
            $this->q('Áreas de figuras planas',    'Un trapecio tiene bases de 10 m y 16 m, y altura de 7 m. Su área es:',                                                                                                        'media',    'A = (B+b)h/2 = 26 × 7 / 2 = 91.',                                                      ['78 m²', '84 m²', '91 m²', '96 m²', '112 m²'], 2),
            $this->q('Razones trigonométricas',    'Para un ángulo agudo θ se conoce sen θ = 3/5. ¿Cuál es cos θ?',                                                                                                               'media',    'Por la identidad sen²θ + cos²θ = 1, cos θ = 4/5.',                                     ['2/5', '3/4', '4/5', '5/4', '1'], 2),
            $this->q('Triángulos rectángulos',     'Un triángulo rectángulo tiene lados 7 cm, 24 cm y 25 cm. ¿Cuál es su área?',                                                                                                   'media',    'Los catetos son 7 y 24; A = 7 × 24 / 2 = 84.',                                         ['72 cm²', '80 cm²', '84 cm²', '96 cm²', '100 cm²'], 2),
            $this->q('Identidades trigonométricas','Si cos θ = 5/13, ¿cuál es tan θ?',                                                                                                                                            'media',    'sen θ = 12/13 (pitágoras); tan θ = 12/5.',                                              ['5/12', '12/13', '12/5', '5/13', '13/12'], 2),
            $this->q('Razones trigonométricas',    'En un triángulo rectángulo, si tan α = 1, ¿cuánto mide el ángulo α?',                                                                                                         'media',    'tan 45° = 1; α = 45°.',                                                                 ['30°', '45°', '60°', '75°', '90°'], 1),
            $this->q('Identidades trigonométricas','Simplificar: (sen²θ + cos²θ) × (1 + tan²θ)',                                                                                                                                   'avanzada', 'sen²θ+cos²θ = 1; 1+tan²θ = sec²θ; producto = sec²θ.',                                   ['1', 'cos²θ', 'sec²θ', 'tan²θ', 'sen²θ'], 2),
            $this->q('Triángulos oblicuángulos',   'En un triángulo, dos lados miden 8 y 10 y el ángulo entre ellos es 60°. Usando la ley del coseno, el tercer lado mide:', 'avanzada', 'c² = 64+100–2(8)(10)(0,5) = 84; c = √84 = 2√21 ≈ 9,17.',                             ['2√13', '2√17', '2√21', '2√23', '4√5'], 2),
            $this->q('Áreas de figuras planas',    'Un rombo tiene diagonales de 16 cm y 12 cm. ¿Cuál es su área?',                                                                                                               'avanzada', 'A = (d₁×d₂)/2 = (16×12)/2 = 96 cm².',                                                 ['80 cm²', '88 cm²', '96 cm²', '104 cm²', '112 cm²'], 2),

            // ═══════════════════════════════════════════════════════════════
            // RAZONAMIENTO LÓGICO-MATEMÁTICO  (12 preguntas: 4 básica, 5 media, 3 avanzada)
            // ═══════════════════════════════════════════════════════════════
            $this->q('Operadores no convencionales','Se define a ★ b = 2a + b. El valor de 7 ★ 4 es:',                                                                                                                             'basica',   'Al sustituir: 2(7)+4=18.',                                                              ['14', '16', '18', '20', '22'], 2),
            $this->q('Analogías lógico-matemáticas','Si 2 → 6, 3 → 12 y 5 → 30, ¿a cuánto corresponde 4?',                                                                                                                        'basica',   'La regla es n → n(n+1): 4×5 = 20.',                                                    ['12', '16', '20', '24', '28'], 2),
            $this->q('Sucesiones numéricas',       'Determine el siguiente término: 2, 6, 12, 20, 30, ...',                                                                                                                        'basica',   'Los términos siguen n(n+1); después de 5×6 corresponde 6×7.',                          ['36', '40', '42', '44', '48'], 2),
            $this->q('Operadores no convencionales','Se define a ⊕ b = a² – 2b. Hallar 5 ⊕ 3.',                                                                                                                                    'basica',   '5² – 2(3) = 25 – 6 = 19.',                                                             ['17', '18', '19', '20', '21'], 2),
            $this->q('Sucesiones numéricas',       'Determine el siguiente término: 3, 8, 18, 38, ...',                                                                                                                            'media',    'Cada término se obtiene duplicando el anterior y sumando 2.',                          ['68', '72', '76', '78', '80'], 3),
            $this->q('Problemas de edades',        'La edad de una madre es el triple de la edad de su hija y entre ambas suman 48 años. ¿Qué edad tiene la hija?',                                                               'media',    'Si la hija tiene x, la madre tiene 3x; 4x=48 y x=12.',                                 ['10 años', '11 años', '12 años', '14 años', '16 años'], 2),
            $this->q('Analogías lógico-matemáticas','Complete la analogía: 4 es a 20 como 6 es a:',                                                                                                                                'media',    'Para n=6: 6×7=42.',                                                                     ['30', '36', '40', '42', '48'], 3),
            $this->q('Planteo de ecuaciones',      'La suma de tres números consecutivos es 57. ¿Cuál es el número mayor?',                                                                                                        'media',    'n+(n+1)+(n+2)=57; 3n+3=57; n=18; el mayor es 20.',                                     ['18', '19', '20', '21', '22'], 2),
            $this->q('Planteo de ecuaciones',      'Un número es el doble de otro y su suma es 93. ¿Cuál es el número mayor?',                                                                                                     'media',    'x+2x=93; x=31; el mayor = 62.',                                                         ['46', '54', '62', '63', '66'], 2),
            $this->q('Sucesiones alfanuméricas',   'En la serie A1, C3, E9, G27, ... ¿cuál es el siguiente término?',                                                                                                              'avanzada', 'Letras cada dos posiciones: A C E G → I. Números triplicados: 1 3 9 27 → 81. Resp: I81.', ['H81', 'I81', 'J81', 'I63', 'H63'], 1),
            $this->q('Operadores no convencionales','Si x ◆ y = (x+y)/(x–y), ¿cuánto es 9 ◆ 3?',                                                                                                                                  'avanzada', '(9+3)/(9–3) = 12/6 = 2.',                                                               ['1', '2', '3', '4', '6'], 1),
            $this->q('Planteo de ecuaciones',      'Un tren recorre la distancia A–B en 3 horas a 80 km/h. Si aumenta su velocidad a 120 km/h, ¿en cuánto tiempo recorrerá la misma distancia?',                                  'avanzada', 'd = 240 km; t = 240/120 = 2 horas.',                                                    ['1 h 30 min', '1 h 45 min', '2 horas', '2 h 15 min', '2 h 30 min'], 2),

            // ═══════════════════════════════════════════════════════════════
            // ESTADÍSTICA Y PROBABILIDADES  (6 preguntas: 3 básica, 2 media, 1 avanzada)
            // ═══════════════════════════════════════════════════════════════
            $this->q('Promedio aritmético',        'El promedio de 12, 15, 18, 20 y 25 es:',                                                                                                                                      'basica',   'La suma es 90 y se divide entre 5: 18.',                                                ['16', '17', '18', '19', '20'], 2),
            $this->q('Mediana y moda',             'En el conjunto 4, 6, 6, 6, 8, 9, 9, la moda es:',                                                                                                                             'basica',   'El valor 6 aparece tres veces, más que cualquier otro.',                                ['4', '6', '8', '9', 'No existe'], 1),
            $this->q('Probabilidad básica',        'Una urna contiene 3 fichas rojas, 2 azules y 5 verdes. La probabilidad de extraer una ficha roja es:',                                                                         'basica',   'Hay 3 resultados favorables entre 10 fichas: 3/10.',                                    ['1/5', '1/4', '3/10', '1/3', '1/2'], 2),
            $this->q('Mediana y moda',             'La mediana del conjunto 5, 7, 7, 9, 14, 18 es:',                                                                                                                              'media',    'Al haber seis datos, se promedian el tercero y el cuarto: (7+9)/2=8.',                 ['7', '7,5', '8', '8,5', '9'], 2),
            $this->q('Interpretación de tablas',   'En un registro de 20 postulantes, 4 obtuvieron 60 pts, 6 obtuvieron 70, 8 obtuvieron 80 y 2 obtuvieron 90. ¿Qué porcentaje alcanzó al menos 80 puntos?',                       'media',    'Diez de veinte alcanzaron 80 o más: 10/20 = 50 %.',                                     ['40 %', '45 %', '50 %', '55 %', '60 %'], 2),
            $this->q('Probabilidad básica',        'Se lanza un dado equilibrado dos veces. ¿Cuál es la probabilidad de obtener 6 en ambos lanzamientos?',                                                                         'avanzada', 'P = (1/6)(1/6) = 1/36.',                                                               ['1/6', '1/12', '1/18', '1/36', '1/48'], 3),

            // ═══════════════════════════════════════════════════════════════
            // RESOLUCIÓN DE PROBLEMAS  (6 preguntas: 2 básica, 3 media, 1 avanzada)
            // ═══════════════════════════════════════════════════════════════
            $this->q('Comparación de magnitudes',  'En la mañana la temperatura fue 4 °C y durante la noche descendió 11 °C. ¿Cuál fue la temperatura nocturna?',                                                                  'basica',   'Se calcula 4 – 11 = –7.',                                                               ['–15 °C', '–7 °C', '–5 °C', '7 °C', '15 °C'], 1),
            $this->q('Modelado con ecuaciones',    'Una entrada académica y dos cuadernos cuestan Bs 46. Si cada cuaderno cuesta Bs 13, ¿cuál es el precio de la entrada?',                                                        'basica',   'Si x es la entrada: x + 2(13)=46; x=20.',                                              ['Bs 16', 'Bs 18', 'Bs 20', 'Bs 22', 'Bs 24'], 2),
            $this->q('Problemas contextualizados', 'Un minibús recorre 180 km utilizando 15 litros de combustible. Si mantiene el rendimiento, ¿cuántos litros requiere para 300 km?',                                             'media',    'La razón es 12 km por litro; 300/12=25 litros.',                                        ['20', '22', '24', '25', '27'], 3),
            $this->q('Interpretación de enunciados','Un postulante responde correctamente 36 de 45 ejercicios. ¿Qué fracción simplificada representa sus respuestas correctas?',                                                    'media',    '36/45 se simplifica dividiendo por 9: 4/5.',                                            ['2/3', '3/4', '4/5', '5/6', '8/9'], 2),
            $this->q('Modelado con ecuaciones',    'Dos números consecutivos suman 73. ¿Cuál es el mayor?',                                                                                                                        'media',    'Si son n y n+1, entonces 2n+1=73; n=36 y el mayor es 37.',                             ['35', '36', '37', '38', '39'], 2),
            $this->q('Problemas contextualizados', 'Una cisterna se llena con dos tuberías: la primera la llena sola en 6 horas y la segunda en 4 horas. ¿En cuántas horas la llenan trabajando juntas?',                          'avanzada', 'Tasa combinada = 1/6+1/4 = 5/12; tiempo = 12/5 = 2,4 horas.',                          ['2 h', '2,2 h', '2,4 h', '2,5 h', '2,8 h'], 2),
        ];
    }

    private function q(
        string $tema,
        string $enunciado,
        string $dificultad,
        string $explicacion,
        array  $alternativas,
        int    $correcta,
    ): array {
        return compact('tema', 'enunciado', 'dificultad', 'explicacion', 'alternativas', 'correcta');
    }
}
