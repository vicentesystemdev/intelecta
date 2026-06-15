<?php

namespace Database\Seeders\Support;

final class AvalanchaAcademicCatalog
{
    public static function subjects(): array
    {
        return [
            'MAT' => [
                'name' => 'Matemática',
                'description' => 'Fundamentos matemáticos para el ingreso a carreras de Ingeniería.',
                'color' => 'indigo',
                'icon' => 'Calculator',
                'question_count' => 40,
                'areas' => [
                    'Álgebra' => [
                        self::topic('Ecuaciones lineales', 'Si 3x + 5 = 20, ¿cuál es el valor de x?', '5', ['3', '4', '6', '8'], 'Se despeja x: 3x = 15, por tanto x = 5.'),
                        self::topic('Ecuaciones cuadráticas', '¿Cuáles son las raíces de x² - 5x + 6 = 0?', '2 y 3', ['1 y 6', '-2 y -3', '3 y 5', '1 y 5'], 'El trinomio se factoriza como (x - 2)(x - 3).'),
                        self::topic('Sistemas de ecuaciones', 'Si x + y = 10 y x - y = 2, ¿cuánto vale x?', '6', ['4', '5', '8', '12'], 'Al sumar ambas ecuaciones se obtiene 2x = 12.'),
                        self::topic('Productos notables', '¿Cuál es el desarrollo de (a + b)²?', 'a² + 2ab + b²', ['a² + b²', 'a² - 2ab + b²', '2a² + 2b²', 'a² + ab + b²'], 'El cuadrado de un binomio incluye el doble producto.'),
                    ],
                    'Aritmética' => [
                        self::topic('Razones y proporciones', 'Si 4 cuadernos cuestan 28 bolivianos, ¿cuánto cuestan 7 cuadernos?', '49 bolivianos', ['35 bolivianos', '42 bolivianos', '56 bolivianos', '63 bolivianos'], 'El precio unitario es 7 bolivianos y 7 × 7 = 49.'),
                        self::topic('Porcentajes', '¿Cuál es el 15% de 240?', '36', ['24', '30', '40', '48'], 'Se calcula 240 × 0,15 = 36.'),
                        self::topic('Potenciación y radicación', '¿Cuál es el valor de √144 + 2³?', '20', ['14', '18', '22', '24'], '√144 = 12 y 2³ = 8; la suma es 20.'),
                    ],
                    'Geometría' => [
                        self::topic('Áreas y perímetros', '¿Cuál es el área de un rectángulo de 8 m por 5 m?', '40 m²', ['13 m²', '26 m²', '35 m²', '80 m²'], 'El área del rectángulo es base por altura.'),
                        self::topic('Triángulos', 'Un triángulo rectángulo tiene catetos de 6 y 8. ¿Cuánto mide la hipotenusa?', '10', ['7', '12', '14', '48'], 'Por Pitágoras: √(6² + 8²) = 10.'),
                        self::topic('Circunferencia', 'Si el radio de una circunferencia es 4 cm, ¿cuál es su longitud?', '8π cm', ['4π cm', '16π cm', '2π cm', '12π cm'], 'La longitud es 2πr = 8π cm.'),
                    ],
                    'Trigonometría' => [
                        self::topic('Razones trigonométricas', 'En un triángulo rectángulo, ¿qué razón representa cateto opuesto entre hipotenusa?', 'Seno', ['Coseno', 'Tangente', 'Cotangente', 'Secante'], 'Por definición, sen θ = cateto opuesto / hipotenusa.'),
                        self::topic('Identidades básicas', '¿Cuál identidad trigonométrica es correcta?', 'sen² θ + cos² θ = 1', ['sen θ + cos θ = 1', 'tan² θ + 1 = 0', 'sen² θ - cos² θ = 1', 'tan θ = cos θ / sen θ'], 'Es la identidad pitagórica fundamental.'),
                        self::topic('Resolución de triángulos', 'Si sen 30° = 1/2 y la hipotenusa mide 12, ¿cuánto mide el cateto opuesto?', '6', ['4', '8', '10', '24'], 'El cateto opuesto es 12 × 1/2 = 6.'),
                    ],
                    'Funciones' => [
                        self::topic('Función lineal', 'En f(x) = 2x + 3, ¿cuál es el valor de f(4)?', '11', ['8', '9', '10', '14'], 'Se reemplaza x por 4: 2(4) + 3 = 11.'),
                        self::topic('Función cuadrática', '¿Cuál es el vértice de f(x) = x² - 4x + 3?', '(2, -1)', ['(-2, -1)', '(2, 1)', '(4, 3)', '(1, 2)'], 'x = -b/2a = 2 y f(2) = -1.'),
                        self::topic('Interpretación gráfica', '¿Qué indica la pendiente positiva de una recta?', 'La función aumenta al crecer x', ['La función es constante', 'La función disminuye al crecer x', 'La recta es vertical', 'La función no tiene intercepto'], 'Una pendiente positiva representa crecimiento.'),
                    ],
                ],
            ],
            'FIS' => [
                'name' => 'Física',
                'description' => 'Modelado de fenómenos físicos fundamentales para Ingeniería.',
                'color' => 'cyan',
                'icon' => 'Atom',
                'question_count' => 35,
                'areas' => [
                    'Cinemática' => [
                        self::topic('Movimiento rectilíneo uniforme', 'Un móvil recorre 150 m en 10 s. ¿Cuál es su rapidez?', '15 m/s', ['10 m/s', '12 m/s', '18 m/s', '25 m/s'], 'En MRU, v = d/t = 150/10.'),
                        self::topic('Movimiento uniformemente acelerado', 'Un móvil parte del reposo con aceleración de 4 m/s². ¿Qué velocidad alcanza en 3 s?', '12 m/s', ['7 m/s', '9 m/s', '16 m/s', '24 m/s'], 'v = v₀ + at = 4 × 3.'),
                        self::topic('Movimiento parabólico', 'Sin resistencia del aire, ¿cómo se comporta la velocidad horizontal de un proyectil?', 'Permanece constante', ['Aumenta linealmente', 'Disminuye hasta cero', 'Cambia con la masa', 'Se invierte en el punto más alto'], 'No existe aceleración horizontal ideal.'),
                    ],
                    'Dinámica' => [
                        self::topic('Leyes de Newton', 'Una fuerza neta de 30 N actúa sobre una masa de 5 kg. ¿Cuál es la aceleración?', '6 m/s²', ['5 m/s²', '10 m/s²', '15 m/s²', '25 m/s²'], 'Por la segunda ley, a = F/m.'),
                        self::topic('Fuerza y masa', '¿Cuál es el peso aproximado de una masa de 8 kg si g = 10 m/s²?', '80 N', ['8 N', '18 N', '40 N', '800 N'], 'El peso es P = mg.'),
                        self::topic('Rozamiento', 'Si μ = 0,2 y la normal es 100 N, ¿cuál es la fuerza de rozamiento?', '20 N', ['10 N', '40 N', '80 N', '120 N'], 'La fricción se calcula como f = μN.'),
                    ],
                    'Trabajo y Energía' => [
                        self::topic('Trabajo mecánico', 'Una fuerza de 25 N desplaza un cuerpo 4 m en su dirección. ¿Qué trabajo realiza?', '100 J', ['21 J', '29 J', '50 J', '125 J'], 'W = Fd = 25 × 4.'),
                        self::topic('Energía cinética', '¿Cuál es la energía cinética de una masa de 2 kg que se mueve a 5 m/s?', '25 J', ['10 J', '20 J', '50 J', '100 J'], 'Ec = 1/2 mv² = 25 J.'),
                        self::topic('Energía potencial', 'Una masa de 3 kg está a 5 m de altura. Con g = 10 m/s², ¿cuál es su energía potencial?', '150 J', ['15 J', '50 J', '100 J', '300 J'], 'Ep = mgh = 3 × 10 × 5.'),
                    ],
                    'Electricidad básica' => [
                        self::topic('Ley de Ohm', 'Una resistencia de 6 Ω se conecta a 18 V. ¿Cuál es la corriente?', '3 A', ['2 A', '6 A', '12 A', '108 A'], 'I = V/R = 18/6.'),
                        self::topic('Circuitos simples', 'Dos resistencias de 4 Ω y 6 Ω conectadas en serie equivalen a:', '10 Ω', ['2,4 Ω', '5 Ω', '24 Ω', '48 Ω'], 'En serie las resistencias se suman.'),
                        self::topic('Potencia eléctrica', 'Un equipo funciona a 20 V y consume 3 A. ¿Cuál es su potencia?', '60 W', ['6 W', '17 W', '23 W', '120 W'], 'P = VI = 20 × 3.'),
                    ],
                    'Hidrostática' => [
                        self::topic('Presión', 'Una fuerza de 200 N actúa sobre 4 m². ¿Cuál es la presión?', '50 Pa', ['25 Pa', '80 Pa', '196 Pa', '800 Pa'], 'P = F/A = 200/4.'),
                        self::topic('Principio de Pascal', '¿Qué dispositivo aplica directamente el principio de Pascal?', 'Prensa hidráulica', ['Péndulo simple', 'Termómetro', 'Transformador', 'Plano inclinado'], 'La presión aplicada a un fluido confinado se transmite íntegramente.'),
                        self::topic('Principio de Arquímedes', 'El empuje sobre un cuerpo sumergido equivale al:', 'Peso del fluido desplazado', ['Peso total del cuerpo', 'Volumen del recipiente', 'Área de la superficie', 'Producto de masa y velocidad'], 'Es el enunciado del principio de Arquímedes.'),
                    ],
                ],
            ],
            'QMC' => [
                'name' => 'Química',
                'description' => 'Fundamentos químicos para ciencias exactas e Ingeniería.',
                'color' => 'emerald',
                'icon' => 'FlaskConical',
                'question_count' => 35,
                'areas' => [
                    'Estructura atómica' => [
                        self::topic('Modelos atómicos', '¿Qué modelo introdujo niveles de energía cuantizados para los electrones?', 'Modelo de Bohr', ['Modelo de Dalton', 'Modelo de Thomson', 'Modelo de Rutherford', 'Modelo cinético'], 'Bohr propuso órbitas con energías definidas.'),
                        self::topic('Número atómico y masa atómica', 'Un átomo con 11 protones tiene número atómico:', '11', ['5', '12', '22', '33'], 'El número atómico es igual al número de protones.'),
                        self::topic('Configuración electrónica', '¿Cuál es la distribución por niveles del sodio, Z = 11?', '2-8-1', ['2-7-2', '2-9', '3-7-1', '8-3'], 'Los once electrones ocupan los niveles 2, 8 y 1.'),
                    ],
                    'Enlace químico' => [
                        self::topic('Enlace iónico', '¿Entre qué tipos de elementos se forma típicamente un enlace iónico?', 'Un metal y un no metal', ['Dos gases nobles', 'Dos metales', 'Dos no metales idénticos', 'Solo elementos líquidos'], 'Implica transferencia de electrones entre metal y no metal.'),
                        self::topic('Enlace covalente', 'En un enlace covalente los átomos:', 'Comparten electrones', ['Pierden protones', 'Comparten neutrones', 'Forman un plasma', 'Transfieren masa'], 'El enlace covalente se forma por pares electrónicos compartidos.'),
                        self::topic('Electronegatividad', 'La electronegatividad mide la capacidad de un átomo para:', 'Atraer electrones de enlace', ['Perder neutrones', 'Aumentar su masa', 'Emitir radiación', 'Cambiar de período'], 'Describe la atracción de electrones compartidos.'),
                    ],
                    'Estequiometría' => [
                        self::topic('Mol', '¿Cuántos moles hay en 36 g de agua si su masa molar es 18 g/mol?', '2 mol', ['0,5 mol', '1 mol', '18 mol', '54 mol'], 'n = m/M = 36/18.'),
                        self::topic('Balanceo de ecuaciones', '¿Cuáles son los coeficientes mínimos de H₂ + O₂ → H₂O?', '2, 1, 2', ['1, 1, 1', '1, 2, 1', '2, 2, 1', '1, 1, 2'], 'La ecuación balanceada es 2H₂ + O₂ → 2H₂O.'),
                        self::topic('Relaciones masa-masa', 'Si 2 mol de H₂ producen 2 mol de H₂O, ¿cuántos moles de agua producen 4 mol de H₂?', '4 mol', ['1 mol', '2 mol', '6 mol', '8 mol'], 'La relación molar H₂:H₂O es 1:1.'),
                    ],
                    'Soluciones' => [
                        self::topic('Concentración molar', 'Una solución contiene 2 mol de soluto en 4 L. ¿Cuál es su molaridad?', '0,5 M', ['0,25 M', '2 M', '4 M', '8 M'], 'M = n/V = 2/4.'),
                        self::topic('Porcentaje en masa', 'Una mezcla tiene 10 g de soluto en 100 g de solución. ¿Cuál es el porcentaje en masa?', '10%', ['1%', '5%', '20%', '90%'], 'El porcentaje es 10/100 × 100.'),
                        self::topic('Diluciones', 'Para diluir una solución sin cambiar la cantidad de soluto se debe:', 'Agregar solvente', ['Agregar más soluto', 'Evaporar todo el solvente', 'Aumentar la presión', 'Cambiar el recipiente'], 'La dilución reduce concentración añadiendo solvente.'),
                    ],
                    'Gases' => [
                        self::topic('Ley de Boyle', 'Un gas ocupa 6 L a 2 atm. A temperatura constante, ¿qué volumen ocupa a 3 atm?', '4 L', ['2 L', '6 L', '9 L', '12 L'], 'P₁V₁ = P₂V₂; V₂ = 12/3.'),
                        self::topic('Ley de Charles', 'A presión constante, si la temperatura absoluta aumenta, el volumen:', 'Aumenta proporcionalmente', ['Disminuye a cero', 'Permanece constante', 'Depende solo de la masa', 'Se vuelve negativo'], 'La ley de Charles relaciona directamente V y T.'),
                        self::topic('Ecuación de gas ideal', '¿Cuál es la ecuación de estado de un gas ideal?', 'PV = nRT', ['P = mV', 'V = IR', 'E = mc²', 'F = ma'], 'Relaciona presión, volumen, cantidad y temperatura.'),
                    ],
                ],
            ],
            'PAA' => [
                'name' => 'Razonamiento Lógico / Aptitud Académica',
                'description' => 'Razonamiento cuantitativo y comprensión de problemas para admisión universitaria.',
                'color' => 'violet',
                'icon' => 'Brain',
                'question_count' => 15,
                'areas' => [
                    'Razonamiento cuantitativo' => [
                        self::topic('Series numéricas', 'Complete la serie: 3, 6, 12, 24, ...', '48', ['30', '36', '42', '54'], 'Cada término duplica al anterior.'),
                        self::topic('Analogías lógicas', 'Motor es a automóvil como corazón es a:', 'Cuerpo humano', ['Carretera', 'Combustible', 'Rueda', 'Velocidad'], 'Ambos son componentes centrales que impulsan un sistema.'),
                        self::topic('Interpretación de problemas', 'Si todos los ingenieros estudian ciencias y Ana estudia Ingeniería, entonces:', 'Ana estudia ciencias', ['Ana no estudia ciencias', 'Todos estudian Ingeniería', 'Ningún ingeniero estudia ciencias', 'No se puede concluir nada'], 'Es una deducción directa de las premisas.'),
                        self::topic('Patrones espaciales', 'Una figura gira 90° a la derecha en cada paso. Después de cuatro pasos queda:', 'En la posición inicial', ['Invertida verticalmente', 'A 90° de la inicial', 'A 180° de la inicial', 'Sin orientación'], 'Cuatro giros de 90° completan 360°.'),
                        self::topic('Suficiencia de datos', 'Para hallar x en x + y = 10, ¿qué dato adicional es suficiente?', 'El valor de y', ['El signo de x', 'Que x sea real', 'Que 10 sea positivo', 'El nombre de la variable'], 'Con y conocido se despeja x de forma única.'),
                    ],
                ],
            ],
        ];
    }

    public static function questions(string $code, array $subject): array
    {
        $definitions = [];

        foreach ($subject['areas'] as $area => $topics) {
            foreach ($topics as $topic) {
                $definitions[] = $topic + ['area' => $area];
            }
        }

        $questions = [];
        $count = (int) $subject['question_count'];
        $round = 0;

        while (count($questions) < $count) {
            foreach ($definitions as $definition) {
                if (count($questions) >= $count) {
                    break;
                }

                $index = count($questions);
                $questions[] = $definition + [
                    'subject_code' => $code,
                    'variant' => $round + 1,
                    'difficulty' => self::difficulty($index, $count),
                    'statement' => self::statement(
                        $definition['prompt'],
                        $definition['name'],
                        $round,
                    ),
                ];
            }

            $round++;
        }

        return $questions;
    }

    private static function topic(
        string $name,
        string $prompt,
        string $correct,
        array $distractors,
        string $explanation,
    ): array {
        return compact('name', 'prompt', 'correct', 'distractors', 'explanation');
    }

    private static function difficulty(int $index, int $total): string
    {
        $ratio = ($index + 1) / $total;

        return match (true) {
            $ratio <= 0.4 => 'basica',
            $ratio <= 0.8 => 'media',
            default => 'avanzada',
        };
    }

    private static function statement(string $prompt, string $topic, int $round): string
    {
        return match ($round % 3) {
            0 => $prompt,
            1 => "Aplicación de {$topic}: {$prompt}",
            default => "Seguimiento de {$topic}: {$prompt}",
        };
    }
}
