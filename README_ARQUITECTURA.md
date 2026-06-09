# Arquitectura de INTELECTA

## Propósito

INTELECTA adopta una organización modular por dominios para mantener separadas las
responsabilidades académicas, institucionales y técnicas del sistema. Cada dominio
representa una capacidad del negocio y agrupa las piezas necesarias para desarrollarla
sin concentrar toda la lógica en controladores, modelos Eloquent o servicios globales.

Esta estructura complementa la organización estándar de Laravel. Los controladores,
Form Requests, middleware y demás adaptadores HTTP permanecen en `app/Http`, mientras
que la lógica propia del negocio se incorpora gradualmente en `app/Domains`.

La adopción es incremental: los modelos existentes de Laravel no se mueven y no es
necesario completar todas las capas cuando un caso de uso sencillo no las requiere.

## Flujo de una operación

El flujo de referencia para una operación de escritura o un caso de uso con reglas de
negocio es:

```text
Controller → FormRequest → DTO → Action → Service → Repository → Model
```

1. **Controller:** recibe la petición HTTP, delega el trabajo y construye la respuesta
   Inertia, JSON o redirección. No contiene reglas de negocio.
2. **FormRequest:** autoriza y valida los datos de entrada en el límite HTTP.
3. **DTO:** convierte la entrada validada en un objeto tipado, explícito e independiente
   del objeto `Request`.
4. **Action:** representa un caso de uso concreto, por ejemplo cerrar una evaluación.
   Coordina los componentes necesarios y delimita la transacción de negocio.
5. **Service:** encapsula reglas o cálculos reutilizables dentro del dominio. Se utiliza
   cuando la lógica supera la responsabilidad de una única Action.
6. **Repository:** abstrae consultas y persistencia cuando aportan una interfaz estable,
   reutilización o aislamiento de consultas complejas.
7. **Model:** representa el estado persistente mediante Eloquent, sus relaciones,
   casts y comportamientos estrictamente asociados a la entidad.

Este flujo es una guía, no una obligación de crear capas vacías. Una Action puede usar
un modelo directamente cuando la operación sea pequeña y no exista una abstracción
útil que justificar.

## Estructura de cada dominio

Cada carpeta en `app/Domains` utiliza el namespace `App\Domains\<Dominio>` y contiene:

| Carpeta | Responsabilidad |
| --- | --- |
| `Actions` | Casos de uso ejecutables y acotados. Una clase por intención de negocio. |
| `DTOs` | Objetos tipados para transportar datos entre capas sin depender de HTTP. |
| `Enums` | Estados, tipos y categorías válidas que pertenecen exclusivamente al dominio. |
| `Models` | Nuevos modelos Eloquent propios del dominio cuando sean incorporados. |
| `Repositories` | Contratos e implementaciones de acceso a datos y consultas especializadas. |
| `Services` | Reglas, cálculos y coordinación reutilizable dentro del dominio. |

Los modelos existentes, como `App\Models\User`, permanecen en su ubicación actual hasta
que exista una decisión arquitectónica específica y una migración planificada.

## Componentes compartidos

`app/Shared` contiene elementos verdaderamente transversales que no pertenecen a un
único dominio:

| Carpeta | Responsabilidad |
| --- | --- |
| `Contracts` | Interfaces técnicas o de aplicación utilizadas por varios dominios. |
| `DTOs` | Estructuras de datos comunes y estables entre dominios. |
| `Enums` | Enumeraciones transversales, no estados particulares de una entidad. |
| `Helpers` | Funciones auxiliares puras y sin reglas específicas del negocio. |
| `Traits` | Comportamientos reutilizables con una responsabilidad técnica clara. |

`Shared` no debe convertirse en un contenedor genérico. Si una pieza expresa lenguaje
o reglas de un dominio, debe permanecer dentro de ese dominio.

## Responsabilidad de los dominios

### Seguridad

Gestiona autenticación aplicada al sistema, usuarios, roles, permisos, autorización y
políticas de acceso. Se integra con Breeze y Spatie Permission sin duplicar sus
responsabilidades. `App\Models\User` se conserva como modelo existente.

### Institucional

Representa la estructura del instituto: carreras, colegios de procedencia, periodos
académicos, sedes y parámetros institucionales relacionados con admisiones.

### Postulantes

Gestiona el registro académico del postulante, su información de admisión, carrera
postulada, procedencia y estado dentro del proceso preuniversitario.

### Evaluaciones

Organiza plantillas, banco de preguntas, instrumentos lógico-matemáticos, programación,
aplicación y ciclo de estados de cada evaluación.

### Resultados

Registra respuestas, puntajes, calificaciones, niveles de desempeño y consolidación de
resultados producidos por las evaluaciones.

### Reportes

Construye consultas e informes institucionales por colegio, carrera, evaluación,
periodo y nivel de desempeño. Su responsabilidad es presentar información consolidada,
no modificar el proceso académico de origen.

### LearningAnalytics

Interpreta datos académicos para generar indicadores, patrones, alertas y recomendaciones.
En su primera etapa consume resultados consolidados; posteriormente podrá incorporar
modelos predictivos, trazabilidad longitudinal y evaluación de riesgo académico.

## Alcance del MVP

El MVP contempla:

- Seguridad basada en usuarios, roles y permisos.
- Gestión institucional básica de carreras y colegios.
- Registro y seguimiento de postulantes.
- Administración de plantillas, preguntas y evaluaciones lógico-matemáticas.
- Cierre de evaluaciones y consolidación de resultados.
- Reportes descriptivos por carrera, colegio, evaluación y desempeño.
- Indicadores iniciales y reglas transparentes de riesgo académico.

El MVP prioriza operaciones trazables y análisis descriptivo. No requiere algoritmos
predictivos para entregar valor institucional.

## Evolución de Learning Analytics

La arquitectura deja preparado `LearningAnalytics` para incorporar de forma progresiva:

- Historial longitudinal de desempeño.
- Segmentación por carrera, colegio y cohorte.
- Detección de patrones y factores de riesgo.
- Recomendaciones académicas basadas en evidencia.
- Modelos predictivos versionados y métricas de calidad.
- Explicabilidad, auditoría y seguimiento de decisiones automatizadas.

Los modelos futuros no deben escribir directamente sobre datos académicos operativos.
Sus resultados deben tratarse como indicadores o recomendaciones, conservar su versión
y permitir revisión institucional.

## Evaluaciones multiasignatura

INTELECTA organiza el banco académico mediante la jerarquía
`Materia → Área de conocimiento → Tema → Pregunta`. La fase actual incorpora
Matemática, Física, Química y Razonamiento Académico como base para evaluaciones
diagnósticas y plantillas de preparación preuniversitaria orientadas a Ingeniería.

Las preguntas pueden registrar subtema, referencia de exigencia, habilidad evaluada,
tiempo estimado y relación con la preparación para Ingeniería. Estas variables permiten
ampliar posteriormente el seguimiento por materia, tema, dificultad, tiempo y resultado
sin afirmar predicciones en la fase actual.

La capa avanzada de Learning Analytics queda preparada para una siguiente fase, cuando
existan aplicaciones de evaluaciones y resultados históricos suficientes para construir
indicadores académicos validados.

## Reglas para nuevos módulos

1. Definir primero el dominio y el caso de uso en lenguaje institucional.
2. Mantener controladores pequeños y libres de reglas de negocio.
3. Validar y autorizar entradas HTTP mediante Form Requests.
4. Usar DTOs cuando los datos atraviesen capas o requieran tipado explícito.
5. Nombrar Actions con una intención concreta, por ejemplo
   `CerrarEvaluacionAction`.
6. Crear Services solo para lógica reutilizable; no usarlos como contenedores genéricos.
7. Incorporar Repositories cuando exista valor real en abstraer persistencia o consultas
   complejas. Evitar envoltorios triviales de Eloquent.
8. Mantener Enums cerca del dominio propietario.
9. Evitar dependencias circulares entre dominios. La coordinación transversal debe
   realizarse mediante contratos, DTOs o eventos bien definidos.
10. Colocar en `Shared` únicamente elementos usados por varios dominios y sin lenguaje
    específico de uno de ellos.
11. Añadir pruebas acordes al riesgo del caso de uso antes de ampliar su integración.
12. No mover clases existentes ni introducir migraciones como parte de una
    reorganización estructural sin una tarea explícita y planificada.

## Convenciones

- Namespace de dominio: `App\Domains\<Dominio>\<Carpeta>`.
- Namespace compartido: `App\Shared\<Carpeta>`.
- Una Action debe exponer una única operación pública principal.
- Los DTOs deben ser inmutables siempre que resulte práctico.
- Las dependencias se reciben por constructor y se resuelven mediante el contenedor de
  Laravel.
- Las transacciones se delimitan en la capa que coordina el caso de uso.
- Las consultas de reportes y analítica no deben introducir efectos secundarios.
