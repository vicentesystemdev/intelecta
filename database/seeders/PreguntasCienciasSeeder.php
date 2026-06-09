<?php

namespace Database\Seeders;

use App\Domains\Evaluaciones\Models\Pregunta;
use App\Domains\Evaluaciones\Models\Tema;
use Illuminate\Database\Seeder;

class PreguntasCienciasSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([...$this->fisica(), ...$this->quimica()] as $item) {
            $tema = Tema::where('nombre_tem', $item['tema'])->firstOrFail();
            $pregunta = Pregunta::updateOrCreate(
                ['enunciado_preg' => $item['enunciado']],
                [
                    'id_tem' => $tema->id_tem,
                    'subtema_preg' => $item['subtema'],
                    'tipo_preg' => 'opcion_multiple',
                    'dificultad_preg' => $item['dificultad'],
                    'exigencia_preg' => $item['exigencia'],
                    'habilidad_preg' => $item['habilidad'],
                    'tiempo_estimado_seg_preg' => $item['tiempo'],
                    'relacion_ingenieria_preg' => $item['relacion'],
                    'puntaje_preg' => 1,
                    'explicacion_preg' => $item['explicacion'],
                    'estado_preg' => 'activo',
                ],
            );

            foreach ($item['alternativas'] as $index => $texto) {
                $letra = chr(65 + $index);
                $pregunta->alternativas()->updateOrCreate(
                    ['letra_alt' => $letra],
                    [
                        'texto_alt' => $texto,
                        'es_correcta_alt' => $index === $item['correcta'],
                        'orden_alt' => $index + 1,
                        'estado_alt' => 'activo',
                    ],
                );
            }
        }
    }

    private function fisica(): array
    {
        $r = 'Aplica modelos físicos fundamentales en el análisis de sistemas de Ingeniería.';

        return [
            $this->q('Metrología e introducción', 'Conversión de unidades', 'Una longitud de 2,5 km equivale a:', 'basica', '2,5 km multiplicado por 1000 equivale a 2500 m.', ['25 m', '250 m', '2500 m', '25 000 m', '0,25 m'], 2, 'Cálculo Operativo', 75, $r),
            $this->q('Metrología e introducción', 'Notación científica', 'La medida 0,00045 m expresada en notación científica es:', 'basica', 'Se desplaza la coma cuatro lugares: 4,5 × 10⁻⁴ m.', ['4,5 × 10⁻² m', '4,5 × 10⁻³ m', '4,5 × 10⁻⁴ m', '45 × 10⁻⁴ m', '0,45 × 10⁻⁵ m'], 2, 'Cálculo Operativo', 90, $r),
            $this->q('Vectores', 'Componentes cartesianas', 'Un vector de 10 N forma 37° con el eje horizontal. Usando cos 37° ≈ 0,8, su componente horizontal es:', 'media', 'La componente horizontal es 10 cos 37° = 8 N.', ['4 N', '6 N', '8 N', '10 N', '12 N'], 2, 'Modelado Fenomenológico', 120, $r),
            $this->q('Vectores', 'Suma de vectores', 'Dos fuerzas perpendiculares de 6 N y 8 N actúan sobre un punto. La magnitud de la resultante es:', 'media', 'Por Pitágoras, R = √(6² + 8²) = 10 N.', ['2 N', '7 N', '10 N', '12 N', '14 N'], 2, 'Cálculo Operativo', 120, $r),
            $this->q('Movimiento en una dimensión', 'Velocidad media', 'Un móvil recorre 180 m en 12 s con movimiento uniforme. Su rapidez es:', 'basica', 'v = d/t = 180/12 = 15 m/s.', ['10 m/s', '12 m/s', '15 m/s', '18 m/s', '20 m/s'], 2, 'Cálculo Operativo', 75, $r),
            $this->q('Movimiento en una dimensión', 'Movimiento acelerado', 'Un automóvil parte del reposo con aceleración constante de 3 m/s². ¿Qué velocidad alcanza en 4 s?', 'media', 'v = v₀ + at = 0 + 3(4) = 12 m/s.', ['7 m/s', '9 m/s', '12 m/s', '15 m/s', '18 m/s'], 2, 'Modelado Fenomenológico', 105, $r),
            $this->q('Movimiento en una dimensión', 'Caída libre', 'Se deja caer un objeto desde el reposo. Si g = 10 m/s², su rapidez después de 3 s es:', 'media', 'v = gt = 10(3) = 30 m/s.', ['10 m/s', '20 m/s', '30 m/s', '40 m/s', '45 m/s'], 2, 'Modelado Fenomenológico', 90, $r),
            $this->q('Movimiento en dos dimensiones', 'Movimiento parabólico', 'En un lanzamiento horizontal ideal, la componente horizontal de la velocidad:', 'basica', 'Sin resistencia del aire no existe aceleración horizontal, por lo que esa componente permanece constante.', ['Aumenta linealmente', 'Disminuye linealmente', 'Permanece constante', 'Se anula en el punto más alto', 'Depende de la masa'], 2, 'Conceptual', 90, $r),
            $this->q('Movimiento en dos dimensiones', 'Alcance horizontal', 'Una esfera se lanza horizontalmente a 5 m/s y permanece 2 s en el aire. Su desplazamiento horizontal es:', 'media', 'x = vₓt = 5(2) = 10 m.', ['2,5 m', '5 m', '10 m', '15 m', '20 m'], 2, 'Cálculo Operativo', 105, $r),
            $this->q('Dinámica de la partícula', 'Segunda ley de Newton', 'Una fuerza neta de 24 N actúa sobre una masa de 6 kg. La aceleración producida es:', 'basica', 'a = F/m = 24/6 = 4 m/s².', ['2 m/s²', '3 m/s²', '4 m/s²', '6 m/s²', '8 m/s²'], 2, 'Cálculo Operativo', 75, $r),
            $this->q('Dinámica de la partícula', 'Fuerza de fricción', 'Un bloque de 10 kg está sobre una superficie horizontal con coeficiente de fricción 0,2. Si g = 10 m/s², la fricción máxima es:', 'media', 'f = μN = 0,2(10 × 10) = 20 N.', ['10 N', '20 N', '25 N', '50 N', '100 N'], 1, 'Modelado Fenomenológico', 135, $r),
            $this->q('Dinámica de la partícula', 'Peso aparente', 'Una persona de 60 kg está en un ascensor que acelera hacia arriba a 2 m/s². Con g = 10 m/s², la normal es:', 'avanzada', 'N - mg = ma, entonces N = m(g+a) = 60(12) = 720 N.', ['480 N', '600 N', '680 N', '720 N', '840 N'], 3, 'Modelado Fenomenológico', 180, $r),
            $this->q('Estática', 'Equilibrio traslacional', 'Para que un cuerpo permanezca en equilibrio traslacional, la suma vectorial de fuerzas debe ser:', 'basica', 'El equilibrio traslacional exige fuerza neta igual a cero.', ['Constante y positiva', 'Igual al peso', 'Igual a cero', 'Perpendicular al movimiento', 'Mayor que la fricción'], 2, 'Conceptual', 75, $r),
            $this->q('Estática', 'Momento de una fuerza', 'Una fuerza perpendicular de 30 N se aplica a 0,4 m del eje de giro. El momento producido es:', 'media', 'τ = Fd = 30(0,4) = 12 N·m.', ['7,5 N·m', '12 N·m', '30 N·m', '40 N·m', '75 N·m'], 1, 'Cálculo Operativo', 105, $r),
            $this->q('Trabajo, energía y potencia', 'Trabajo mecánico', 'Una fuerza constante de 50 N desplaza un objeto 4 m en su misma dirección. El trabajo realizado es:', 'basica', 'W = Fd cos 0° = 50(4) = 200 J.', ['12,5 J', '46 J', '54 J', '200 J', '250 J'], 3, 'Cálculo Operativo', 75, $r),
            $this->q('Trabajo, energía y potencia', 'Energía cinética', 'La energía cinética de una masa de 2 kg que se mueve a 6 m/s es:', 'media', 'Ec = 1/2 mv² = 1/2(2)(36) = 36 J.', ['12 J', '18 J', '24 J', '36 J', '72 J'], 3, 'Cálculo Operativo', 105, $r),
            $this->q('Trabajo, energía y potencia', 'Conservación de energía', 'Un cuerpo cae sin rozamiento desde una altura de 5 m. Con g = 10 m/s², su rapidez al llegar al suelo es:', 'avanzada', 'mgh = 1/2 mv²; v = √(2gh) = √100 = 10 m/s.', ['5 m/s', '7,1 m/s', '10 m/s', '12,5 m/s', '50 m/s'], 2, 'Modelado Fenomenológico', 165, $r),
            $this->q('Electricidad básica', 'Ley de Ohm', 'Una resistencia de 6 Ω se conecta a una diferencia de potencial de 12 V. La corriente es:', 'basica', 'I = V/R = 12/6 = 2 A.', ['0,5 A', '2 A', '6 A', '18 A', '72 A'], 1, 'Cálculo Operativo', 75, $r),
            $this->q('Electricidad básica', 'Resistencias en serie', 'Tres resistencias de 2 Ω, 3 Ω y 5 Ω están conectadas en serie. La resistencia equivalente es:', 'media', 'En serie las resistencias se suman: 2 + 3 + 5 = 10 Ω.', ['1 Ω', '5 Ω', '6 Ω', '10 Ω', '30 Ω'], 3, 'Cálculo Operativo', 90, $r),
            $this->q('Electricidad básica', 'Potencia eléctrica', 'Un dispositivo opera con 220 V y consume 2 A. Su potencia eléctrica es:', 'avanzada', 'P = VI = 220(2) = 440 W.', ['110 W', '220 W', '440 W', '880 W', '44 W'], 2, 'Cálculo Operativo', 105, $r),
        ];
    }

    private function quimica(): array
    {
        $r = 'Desarrolla criterios de análisis de materiales y procesos relevantes para Ingeniería.';

        return [
            $this->q('Conceptos fundamentales', 'Propiedades de la materia', '¿Cuál de las siguientes es una propiedad intensiva de la materia?', 'basica', 'La densidad no depende de la cantidad de materia presente.', ['Masa', 'Volumen', 'Longitud', 'Densidad', 'Cantidad de sustancia'], 3, 'Conceptual', 75, $r),
            $this->q('Conceptos fundamentales', 'Cambios físicos y químicos', '¿Cuál situación representa un cambio químico?', 'basica', 'La oxidación forma nuevas sustancias, como óxidos de hierro.', ['Fusión del hielo', 'Evaporación del agua', 'Oxidación del hierro', 'Trituración de sal', 'Disolución de azúcar'], 2, 'Conceptual', 75, $r),
            $this->q('Conceptos fundamentales', 'Separación de mezclas', 'El método más adecuado para separar arena insoluble de agua es:', 'basica', 'La filtración retiene el sólido insoluble y permite pasar el líquido.', ['Destilación', 'Filtración', 'Sublimación', 'Cromatografía', 'Cristalización'], 1, 'Conceptual', 75, $r),
            $this->q('Estructura atómica', 'Partículas subatómicas', 'El número atómico de un elemento corresponde al número de:', 'basica', 'El número atómico Z indica la cantidad de protones del núcleo.', ['Neutrones', 'Nucleones', 'Protones', 'Isótopos', 'Orbitales'], 2, 'Conceptual', 75, $r),
            $this->q('Estructura atómica', 'Iones', 'Un átomo neutro con 17 protones que gana un electrón forma un ion con carga:', 'media', 'Al ganar un electrón queda con una carga neta de -1.', ['+2', '+1', '0', '-1', '-2'], 3, 'Cálculo Operativo', 90, $r),
            $this->q('Estructura atómica', 'Configuración electrónica', 'La distribución electrónica por niveles del sodio, Z = 11, es:', 'media', 'Los once electrones se distribuyen 2, 8 y 1 en los tres primeros niveles.', ['2-7-2', '2-8-1', '3-7-1', '2-9', '8-3'], 1, 'Cálculo Operativo', 105, $r),
            $this->q('Tabla periódica', 'Grupos químicos', 'Los elementos del grupo 18 de la tabla periódica se denominan:', 'basica', 'El grupo 18 corresponde a los gases nobles.', ['Halógenos', 'Alcalinos', 'Calcógenos', 'Gases nobles', 'Metales de transición'], 3, 'Conceptual', 75, $r),
            $this->q('Tabla periódica', 'Tendencias periódicas', 'En un período de la tabla periódica, la electronegatividad generalmente:', 'media', 'La carga nuclear efectiva aumenta hacia la derecha y eleva la electronegatividad.', ['Disminuye hacia la derecha', 'Aumenta hacia la derecha', 'Permanece constante', 'Solo depende de la masa', 'Se anula en los halógenos'], 1, 'Conceptual', 105, $r),
            $this->q('Tabla periódica', 'Radio atómico', '¿Cuál de los siguientes elementos presenta mayor radio atómico?', 'avanzada', 'El radio crece hacia abajo y hacia la izquierda; el potasio es el mayor entre las opciones.', ['Li', 'Na', 'K', 'Cl', 'F'], 2, 'Conceptual', 135, $r),
            $this->q('Nomenclatura inorgánica', 'Óxidos', 'El compuesto Na₂O se nombra:', 'basica', 'Es el óxido formado por sodio con estado de oxidación +1.', ['Peróxido de sodio', 'Óxido de sodio', 'Hidróxido de sodio', 'Sulfato de sodio', 'Cloruro de sodio'], 1, 'Conceptual', 75, $r),
            $this->q('Nomenclatura inorgánica', 'Ácidos', 'La fórmula del ácido clorhídrico es:', 'basica', 'El ácido clorhídrico corresponde al cloruro de hidrógeno en disolución: HCl.', ['HCl', 'HClO', 'HClO₂', 'H₂SO₄', 'HNO₃'], 0, 'Conceptual', 75, $r),
            $this->q('Nomenclatura inorgánica', 'Sales', 'El nombre de CaCO₃ es:', 'media', 'El compuesto está formado por Ca²⁺ y el anión carbonato CO₃²⁻.', ['Clorato de calcio', 'Carbonito de calcio', 'Carbonato de calcio', 'Carburo de calcio', 'Bicarbonato de calcio'], 2, 'Conceptual', 90, $r),
            $this->q('Gases ideales', 'Ley de Boyle', 'A temperatura constante, un gas ocupa 4 L a 2 atm. Si la presión aumenta a 4 atm, el nuevo volumen es:', 'media', 'P₁V₁ = P₂V₂; V₂ = (2 × 4)/4 = 2 L.', ['1 L', '2 L', '4 L', '6 L', '8 L'], 1, 'Modelado Fenomenológico', 120, $r),
            $this->q('Gases ideales', 'Ecuación de estado', 'Para un mol de gas ideal a 273 K y 1 atm, el volumen aproximado es:', 'basica', 'En condiciones normales, un mol de gas ideal ocupa cerca de 22,4 L.', ['2,24 L', '11,2 L', '22,4 L', '24,0 L', '44,8 L'], 2, 'Conceptual', 90, $r),
            $this->q('Gases ideales', 'Ley de Charles', 'A presión constante, un gas ocupa 3 L a 300 K. ¿Qué volumen ocupará a 400 K?', 'avanzada', 'V₁/T₁ = V₂/T₂; V₂ = 3(400)/300 = 4 L.', ['2,0 L', '3,0 L', '4,0 L', '5,0 L', '12,0 L'], 2, 'Modelado Fenomenológico', 135, $r),
            $this->q('Reacciones químicas', 'Balanceo', 'En la ecuación H₂ + O₂ → H₂O, los coeficientes mínimos enteros son:', 'media', 'La ecuación balanceada es 2H₂ + O₂ → 2H₂O.', ['1, 1, 1', '2, 1, 2', '1, 2, 1', '2, 2, 1', '1, 1, 2'], 1, 'Cálculo Operativo', 105, $r),
            $this->q('Reacciones químicas', 'Tipos de reacción', 'La reacción CaCO₃ → CaO + CO₂ se clasifica como:', 'media', 'Un compuesto se separa en sustancias más simples, por lo que es una descomposición.', ['Síntesis', 'Sustitución simple', 'Doble sustitución', 'Descomposición', 'Neutralización'], 3, 'Conceptual', 90, $r),
            $this->q('Estequiometría básica', 'Masa molar', 'La masa molar del agua H₂O, usando H = 1 y O = 16, es:', 'basica', '2(1) + 16 = 18 g/mol.', ['16 g/mol', '17 g/mol', '18 g/mol', '20 g/mol', '32 g/mol'], 2, 'Cálculo Operativo', 75, $r),
            $this->q('Estequiometría básica', 'Cantidad de sustancia', '¿Cuántos moles hay en 36 g de agua si su masa molar es 18 g/mol?', 'media', 'n = m/M = 36/18 = 2 mol.', ['0,5 mol', '1 mol', '2 mol', '18 mol', '54 mol'], 2, 'Cálculo Operativo', 90, $r),
            $this->q('Estequiometría básica', 'Reactivo limitante', 'En 2H₂ + O₂ → 2H₂O, si reaccionan 4 mol de H₂ con 1 mol de O₂, el reactivo limitante es:', 'avanzada', 'Un mol de O₂ consume 2 mol de H₂; queda H₂ en exceso y el O₂ limita la reacción.', ['H₂', 'O₂', 'H₂O', 'Ambos por igual', 'Ninguno'], 1, 'Modelado Fenomenológico', 165, $r),
        ];
    }

    private function q(
        string $tema,
        string $subtema,
        string $enunciado,
        string $dificultad,
        string $explicacion,
        array $alternativas,
        int $correcta,
        string $habilidad,
        int $tiempo,
        string $relacion,
    ): array {
        return compact(
            'tema',
            'subtema',
            'enunciado',
            'dificultad',
            'explicacion',
            'alternativas',
            'correcta',
            'habilidad',
            'tiempo',
            'relacion',
        ) + ['exigencia' => 'Contenido Común'];
    }
}
