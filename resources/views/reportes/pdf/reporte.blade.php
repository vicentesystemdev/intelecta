<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $reporte['titulo'] }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            line-height: 1.45;
        }

        .header {
            background: #16213e;
            border-radius: 10px;
            color: #ffffff;
            margin-bottom: 18px;
            padding: 18px 22px;
        }

        .brand {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }

        .subtitle {
            color: #dbeafe;
            font-size: 10px;
            margin: 0;
        }

        .report-title {
            color: #16213e;
            font-size: 18px;
            font-weight: 800;
            margin: 0 0 6px;
        }

        .description {
            color: #475569;
            margin: 0 0 12px;
        }

        .meta {
            color: #475569;
            font-size: 9px;
            margin-bottom: 14px;
        }

        .summary {
            margin: 0 0 16px;
            width: 100%;
        }

        .summary-card {
            border: 1px solid #dbe3ef;
            border-radius: 8px;
            padding: 9px 11px;
        }

        .summary-label {
            color: #64748b;
            font-size: 8px;
            font-weight: 700;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .summary-value {
            color: #16213e;
            font-size: 13px;
            font-weight: 800;
            margin-top: 3px;
        }

        table.data {
            border-collapse: collapse;
            table-layout: fixed;
            width: 100%;
        }

        table.data th {
            background: #eaf1f8;
            border: 1px solid #cbd5e1;
            color: #16213e;
            font-size: 8px;
            font-weight: 800;
            padding: 7px 6px;
            text-align: left;
            text-transform: uppercase;
        }

        table.data td {
            border: 1px solid #e2e8f0;
            padding: 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        table.data tr:nth-child(even) td {
            background: #f8fafc;
        }

        .empty {
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            color: #64748b;
            padding: 22px;
            text-align: center;
        }

        .footer {
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 8px;
            margin-top: 18px;
            padding-top: 8px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">INTELECTA</div>
        <p class="subtitle">Academia Universitaria Avalancha</p>
        <p class="subtitle">Sistema de Evaluacion y Seguimiento Academico</p>
    </div>

    <h1 class="report-title">{{ $reporte['titulo'] }}</h1>
    <p class="description">{{ $reporte['descripcion'] }}</p>
    <div class="meta">
        Generado el {{ $reporte['generado_en']->format('d/m/Y H:i') }}
    </div>

    @if (! empty($reporte['resumen']))
        <table class="summary">
            <tr>
                @foreach ($reporte['resumen'] as $label => $value)
                    <td class="summary-card">
                        <div class="summary-label">{{ $label }}</div>
                        <div class="summary-value">{{ $value }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    @if (collect($reporte['filas'])->isEmpty())
        <div class="empty">
            No existen registros disponibles para este reporte.
        </div>
    @else
        <table class="data">
            <thead>
                <tr>
                    @foreach ($reporte['columnas'] as $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($reporte['filas'] as $fila)
                    <tr>
                        @foreach ($reporte['columnas'] as $key => $label)
                            <td>{{ $fila[$key] ?? '' }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Reporte generado automaticamente por INTELECTA. Informacion de uso academico e institucional.
    </div>
</body>
</html>
