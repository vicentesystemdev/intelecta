# Fase 2: integración real Laravel ↔ FastAPI

Verificada el 19/10/2026 (Bolivia) en `feature/integracion-cat-lab`. Esta fase conecta el instrumento independiente `MAT-CAT-DEMO-v1`; no modifica el flujo convencional ni requiere migraciones.

## Autoridad del contrato y compatibilidad

Fuente leída: `C:/laragon/www/intelecta_lab/docs/contrato_integracion.md`. SHA-256 del documento inspeccionado: `91de266dc363d5e0385d6c63bcd95b8d36bdb98a311fe1987051bb578bcc06f5`.

- POST `/api/integracion/cat/evaluar`, base local `http://127.0.0.1:8010`.
- Autenticación mediante `X-Service-Token`.
- `contrato_version=1`, `motor_version=cat-d1-eap-v1`, `parametros_version=simulados-demo-v1`.
- Pool id_preg 1–16; D=1; minimum=8, maximum=10, threshold=0.45.
- Vencimiento 30 minutos impuesto por Laravel.
- Identificador persistente y eco: `id_intento_cat`.

No se cambiaron endpoints ni esquemas Python. `MotorCat`, `ContextoCat` y `DecisionCat` mantienen su función de puerto interno PHP; el adaptador los traduce al contrato final. No se escribieron archivos en INTELECTA LAB.

El contrato inicialmente indicaba no publicar theta/SD al navegador. El usuario aprobó expresamente **theta, SD e intervalo solo después de finalizar y exclusivamente para el propietario**. La página existente ya aplica esa condición: las métricas siguen siendo null durante el intento. Claves, parámetros, snapshot e historial psicométrico permanecen privados siempre. Esto es política de presentación Laravel, sin alterar el protocolo Python.

## Cliente y configuración

`MotorCatHttp` sustituye el binding predeterminado de `MotorCatPendiente`. Solicita JSON, deshabilita redirecciones y fija tiempos acotados de conexión/respuesta. No mantiene transacciones de BD abiertas durante la petición. No añade retries implícitos: recuperar reenvía el mismo historial confirmado al motor stateless.

Variables en `config/services.php` y nombres documentados sin credenciales en `.env.example`:

```dotenv
CAT_SERVICE_URL=http://127.0.0.1:8010
CAT_SERVICE_TOKEN=
CAT_CONNECT_TIMEOUT=2
CAT_TIMEOUT=10
```

El secreto real se configuró únicamente en `.env` local, ignorado por Git, y en el entorno del proceso Python como `INTELECTA_CAT_TOKEN`. No se guarda en código, manifiesto, fixture, informe, cabecera del navegador ni frontend. No se debe copiar un token de pruebas unitarias a un servicio real.

Para reiniciar Python hay que proporcionar al proceso el mismo secreto local y ejecutar uvicorn desde el LAB con su runtime existente, manteniendo bind a 127.0.0.1:8010. No basta con arrancar un proceso sin token: el endpoint falla cerrado. El arranque realizado para esta verificación no habilita inicio automático tras reiniciar Windows. El proceso se dejó activo al terminar; no hubo necesidad de pedir al usuario que lo levantara.

Sin token/URL válida el cliente falla cerrado antes de llamar. Rechaza URLs con credenciales, query, fragmento o rutas adicionales; añade únicamente el endpoint contractual. Token inválido, rechazo de contrato, indisponibilidad y fallo de transporte se conservan como códigos técnicos privados (`http_401`, `http_409`, `http_422`, `fallo_transporte`, etc.). Nunca propaga cuerpos remotos, cabeceras o excepciones encadenadas al estudiante o a los logs. Se inspeccionaron fuentes y logs disponibles sin encontrar el secreto real.

## Dos huellas, sin alterar parámetros o snapshots

`ContratoCat::parametrosHash()` reproduce la serialización canónica de Claude: objetos con claves D/calibrado/experimental/items/parametros_version, ítems por id_preg con a/b/c como cadenas de seis decimales, JSON compacto UTF-8 y normalización de −0.000000. El hash observado coincide exactamente:

```text
f1abb3f562696e7a15d0a2e894bd8916bb2661fd263334f281a549f06c13d6c8
```

Se copió el manifiesto oficial, sin regenerarlo, a `resources/cat/laravel_mat_demo_v1.json`; su SHA-256 de archivo coincide con el del LAB: `48adae0d1f77b2a97a629d82f835eb03f5cb3c66c18396f0b969f02aa65ec996`. No existe dependencia de esa carpeta externa en ejecución.

Las solicitudes se construyen desde el snapshot privado de cada intento. Se redondea únicamente la representación de transporte a seis decimales, tolerando ruido IEEE-754 menor que 1e-12; no se actualizan parámetros almacenados. Se rechaza cualquier diferencia sustantiva o hash distinto al manifiesto antes de enviar.

`banco_version` se calcula aparte, desde el contenido académico del snapshot, conservando orden de listas y ordenando claves de objetos. Se excluyen parámetros, configuración e identidad del intento. No se confunde con `huella_snapshot` de Fase 1, que también cubre configuración/parámetros, y **no se reescriben snapshots existentes**.

La solicitud contiene exclusivamente versiones/contrato, id_intento_cat/revision, banco_id/banco_version, flags experimentales, parametros_version/hash, ítems `{id_preg,a,b,c}`, config CAT e historial `{id_preg,correcta,orden}`. No incluye enunciados, alternativas, id_alt, claves, nombres ni correos.

## Verificación de la respuesta y persistencia

El cliente exige HTTP 200 con JSON de tamaño acotado, campos completos y tipos estrictos. Verifica ecos de contrato, intento, revisión, motor, banco_version y parametros_version; respondidas debe coincidir con el historial enviado. Rechaza métricas no finitas, SD negativa, intervalo inválido, ítems desconocidos/repetidos y motivos incompatibles con la configuración y número de respuestas.

Se conserva la secuencia transaccional de Fase 1: guardar respuesta y revisión → confirmar → calcular → aplicar solo si revisión/estado siguen vigentes. Un fallo conserva la aceptación y deja cálculo pendiente. Duplicados idénticos se reproducen; duplicados conflictivos/revisión obsoleta reciben 409. Vencimiento y propiedad siguen siendo responsabilidad Laravel. Los resultados CAT no se incorporan a tablas o promedios convencionales.

## Pruebas ejecutadas

Comando de regresión ejecutado con PHP 8.5.11, pdo_sqlite, mbstring, openssl y curl, y `CAT_REAL_TESTS=1`:

```powershell
php vendor/phpunit/phpunit/phpunit tests/Feature/Cat tests/Feature/Resultados/ResultadosTrazablesTest.php tests/Feature/Portal/StableStudentAccessTest.php tests/Feature/Evaluaciones/EvaluacionesModuleTest.php --do-not-cache-result
```

Resultado: **90 pruebas satisfactorias, 920 aserciones**.

- 26 casos del cliente: canonicalización, independencia de huella de contenido, payload mínimo, header, fallos previos a red, transporte/401/503/409/422/413/302 y validación estricta de ecos/métricas.
- 6 pruebas opt-in contra FastAPI REAL, sin `Http::fake` ni `MotorCatSimulado`: ciclo de diez ítems, recarga, duplicados, revisión conflictiva, token incorrecto con recuperación, conexión rechazada con respuesta conservada, vencimiento, secuencia adulterada y ramas EAP verificadas.
- 18 pruebas previas CAT y 40 regresiones de resultados, acceso estudiantil y módulo convencional.
- Cada prueba de persistencia usa SQLite en memoria y fuerza esa conexión antes de migraciones/fixtures. No ejecuta migraciones ni fixtures en PostgreSQL principal.
- Pint y `git diff --check` satisfactorios. No hubo cambios JavaScript en esta fase; se verificó en navegador el bundle de Fase 1 servido por Laravel con el cliente HTTP nuevo.

Evidencias numéricas del motor real:

| Historial | Siguiente ítem | Theta / SD aproximados |
|---|---:|---|
| Vacío | 8 | Prior EAP |
| 8 correcta | 12 | 0.38262 / 0.90995 |
| 8 incorrecta | 4 | −0.58851 / 0.83248 |
| Secuencia inicial falsificada con 1 | HTTP 409 | Sin aceptación |
| Token incorrecto | HTTP 401 | Respuesta Laravel conservada cuando ya se había aceptado |

El reporte de Claude de 74 pruebas Python satisfactorias y cinco omitidas fue comunicado por el usuario; esta fase no volvió a ejecutar ni modificar esa suite del LAB.

## Evaluación completa desde el navegador

Entorno de verificación: `http://127.0.0.1:8020`, cuenta ficticia local sin acceso a la principal. Se copiaron en modo lectura las **16 preguntas reales, alternativas, clasificación e instrumento publicado** desde PostgreSQL a un SQLite privado nuevo. Se migró exclusivamente ese archivo vacío y se creó la identidad de prueba allí; no se modificaron identidades, snapshots ni datos académicos originales.

Se operó el formulario de login y cada pregunta desde el navegador conectado. Laravel corrigió las selecciones, persistió cada respuesta y consultó el FastAPI real en 8010.

- Intento local: `id_intento_cat=1`.
- Secuencia real: **8 → 12 → 11 → 15 → 16 → 14 → 13 → 10 → 9 → 7**.
- Se recargó tras la primera respuesta y se conservó revisión/pregunta.
- Se detuvo solo el proceso Python iniciado para esta prueba antes de confirmar la segunda respuesta. Laravel conservó **2 respuestas** y mostró cálculo pendiente, también tras recargar.
- Tras reiniciar Python con el mismo secreto, «Reintentar cálculo» reconstruyó el historial y permitió continuar sin duplicar respuestas.
- Final: **10 respuestas, 10 aciertos, estado finalizada**, «Máximo de preguntas alcanzado».
- Theta final: **2.1360325377**; SD: **0.6297841467**; intervalo: **[0.9250966818, 3.4097178457]**. Son resultados experimentales, no una nota ni calibración empírica. No alcanzar threshold=0.45 a diez ítems es consistente con la limitación documentada por Claude.
- Huella de contenido del intento de navegador: `313444e1d7aa31481f0b974b88903bea2462f6269c743b345040c8fffef30802`.
- Tablas convencionales del entorno aislado: cero evaluaciones aplicadas y cero rendimientos tras finalizar.

Artefactos privados, ignorados por Git:

- `storage/app/private/cat/evidencia-fase2.json`: resumen sin claves ni secretos, secuencia, huellas y resultado.
- `storage/app/private/cat/resultado-real-fase2.jpg`: captura final del navegador.
- `storage/app/private/cat/fase2-e2e-de7f65e668.sqlite`: base aislada con el intento verificable. Contiene snapshots y claves privadas; no publicar ni adjuntar el archivo.
- Logs de arranque del proceso LAB en `storage/app/private/cat/`, sin credenciales. La base y evidencia permanecen disponibles para revisión local.

## Lista exacta de archivos de esta fase

Versionables:

1. `.env.example`.
2. `config/services.php`.
3. `app/Providers/CatServiceProvider.php`.
4. `app/Domains/Cat/Exceptions/MotorCatNoDisponible.php`.
5. `app/Domains/Cat/Services/IntentoCatService.php`.
6. `app/Domains/Cat/Services/ContratoCat.php` (nuevo).
7. `app/Domains/Cat/Services/MotorCatHttp.php` (nuevo).
8. `resources/cat/laravel_mat_demo_v1.json` (copia oficial nueva).
9. `tests/Support/ContextoCatHttp.php` (nuevo).
10. `tests/Feature/Cat/MotorCatHttpTest.php` (nuevo).
11. `tests/Feature/Cat/CatRealHttpTest.php` (nuevo).
12. `docs/integracion-cat/FASE2_INTEGRACION_REAL.md` (este informe).

Configuración local adicional: `.env`, ignorado por Git, con el secreto y URL del servicio. Los cinco archivos previos de auditoría/inventario sin commit se conservaron aparte y no forman parte de los commits de Fase 2.

## Bloqueos y pendientes

No hay bloqueo funcional observado para el MVP local con los procesos activos. Pendientes operativos: supervisar/reiniciar FastAPI con su entorno autenticado, verificar disponibilidad al abrir la demostración y mantener protegidos `.env` y los snapshots. El token no se transmite al navegador; para cambiarlo hay que coordinar ambos procesos sin publicarlo.

La evaluación de navegador se realizó en una copia aislada de datos reales, no bajo la identidad de un estudiante real en PostgreSQL principal. No se probó concurrencia multiworker PostgreSQL en esta fase; permanecen los locks, índices y validaciones de revisión de Fase 1, con pruebas automatizadas de respuesta obsoleta. No se ejecutaron migraciones PostgreSQL ni se alteraron snapshots existentes. No se hizo push.
