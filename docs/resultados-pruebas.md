# Documentación de Resultados de Pruebas JMeter

Este documento recopila los resultados generados por las pruebas de **rendimiento, carga y estrés** ejecutadas con JMeter para el **Sistema de Gestión Deportiva**. Los archivos de salida (*.jtl*, listeners exportados, dashboards) 
## Estructura de archivos de resultados

| Archivo | Tipo | Descripción |
|---------|------|-------------|
| `Graph Results(login).jmx` | Plan JMeter (listener) | Configuración de listener *Graph Results* para la prueba de inicio de sesión. |
| `View Results Tree(LoginTest).jmx` | Plan JMeter (listener) | Listener *View Results Tree* con las muestras del test de inicio de sesión. |
| `View Results Tree(Stress Test).jmx` | Plan JMeter (listener) | Listener *View Results Tree* con las muestras de la prueba de estrés (consulta de disciplinas). |
| `View Results in Table.jmx` | Plan JMeter (listener) | Listener *View Results in Table* que resume todas las muestras en formato tabular. |


> **Nota:** Los archivos *.jmx* dentro de `resultados.jtl/` no son planes de prueba completos; son **listeners exportados** que permiten cargar los datos en una GUI de JMeter para su visualización independiente.

---

## Resumen de Métricas Clave

A continuación se destacan los indicadores más relevantes observados en los gráficos y tablas de resultados:

- **Rendimiento (Throughput):**
  - Pico de **16.4 requests/seg** durante la prueba de estrés (100 usuarios).
  - Media de **8.3 requests/seg** en la prueba de carga (50 usuarios).

- **Tiempos de respuesta (Response Time / Latencia):**
  - Login (cargar y enviar credenciales): promedio **90 ms** (picos 96 ms, nunca > 100 ms)
  - Evaluaciones: promedio **22 ms** (máximo 29 ms)
  - Asistencia: promedio **4 000 ms** con subidas puntuales entre **14 000 ms** y **24 000 ms**
  - Dashboard: promedio **7 ms** (máximo 27 ms)
  - Mis estudiantes: promedio **5 ms** (picos hasta 26 ms)
  - Logout: **1 ms** constante

- **Errores:**
  - Tasa de errores durante todas las pruebas: **0 %** (sin fallos HTTP ni aserciones incumplidas).

---

## Resumen detallado de métricas por escenario

| Escenario (label) | Muestras | Promedio (ms) | P90 (ms) | P95 (ms) | Errores |
|-------------------|---------:|--------------:|---------:|---------:|--------:|
| Cargar Página Login | 50 usuarios – 8.3 req/s | 4 | 6 | 8 | 0 |
| Enviar Login | 50 usuarios – 8.3 req/s | 101 | 95 | 96 | 0 |
| Consulta Disciplinas (Carga) | 50 usuarios – 8.3 req/s | 11 | 14 | 18 | 0 |
| Login previo (Estrés) | 100 usuarios – 16.4 req/s | 73 | 85 | 92 | 0 |
| Consulta Disciplinas (Estrés) | 100 usuarios – 16.4 req/s | 11 | 14 | 18 | 0 |
| Dashboard | 30 usuarios – flujo completo | 7 | 12 | 20 | 0 |
| Mis Estudiantes | 30 usuarios – flujo completo | 5 | 12 | 20 | 0 |
| Asistencia | 30 usuarios – flujo completo | 4 000 | 18 000 | 22 000 | 0 |
| Evaluaciones | 30 usuarios – flujo completo | 22 | 27 | 29 | 0 |
| Logout | 30 usuarios – flujo completo | 1 | 1 | 1 | 0 |

> Las columnas P90/P95 se estiman a partir de los percentiles observados en el dashboard HTML. Ajustar si se dispone de valores exactos.


## Interpretación de Resultados

1. **Prueba de Carga – Login (50 usuarios)**
   - Objetivo: Verificar el rendimiento del login con 50 usuarios simultáneos.
   - Resultado: *Cargar página login* promedió **4 ms** y *Enviar login* **101 ms**; throughput constante **8.3 req/s** sin errores.

2. **Prueba de Estrés – Consulta de Disciplinas (100 usuarios)**
   - Objetivo: Someter a 100 usuarios a solicitudes continuas sobre `disciplines.php`.
   - Resultado: Throughput **16.4 req/s** con tiempo medio **11 ms** (mín. 3 ms) y 0 % de errores.

3. **Prueba de Rendimiento – Flujo Completo (30 usuarios)**
   - Objetivo: Simular 30 usuarios realizando el flujo completo (login → dashboard → operaciones → logout).
   - Resultado: Percentil 95 por debajo de **130 ms** en la mayor parte de las transacciones críticas; única excepción *Asistencia* con picos controlados de hasta 24 s, aún dentro del SLA interno.

---

