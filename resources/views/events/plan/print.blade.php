<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>План події - {{ $event->title }} - {{ $event->date->format('d.m.Y') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 12pt;
            line-height: 1.5;
            color: #1a1a1a;
            padding: 20mm;
        }

        @media print {
            body {
                padding: 0;
            }
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3b82f6;
        }

        .church-name {
            font-size: 14pt;
            color: #666;
            margin-bottom: 5px;
        }

        .event-title {
            font-size: 24pt;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .event-date {
            font-size: 16pt;
            color: #3b82f6;
        }

        .event-time {
            font-size: 14pt;
            color: #666;
        }

        .plan-items {
            margin-top: 20px;
        }

        .plan-item {
            display: flex;
            align-items: flex-start;
            padding: 12px 0;
            border-bottom: 1px solid #e5e5e5;
        }

        .plan-item:last-child {
            border-bottom: none;
        }

        .time-column {
            width: 80px;
            flex-shrink: 0;
            text-align: center;
        }

        .time-start {
            font-size: 14pt;
            font-weight: bold;
        }

        .time-end {
            font-size: 10pt;
            color: #666;
        }

        .time-duration {
            font-size: 9pt;
            color: #999;
        }

        .divider {
            width: 3px;
            background-color: #3b82f6;
            margin: 0 15px;
            border-radius: 2px;
            align-self: stretch;
        }

        .content-column {
            flex: 1;
            min-width: 0;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .item-type {
            font-size: 9pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #3b82f6;
            margin-bottom: 2px;
        }

        .item-title {
            font-size: 13pt;
            font-weight: 600;
            margin-bottom: 4px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .item-description {
            font-size: 10pt;
            color: #666;
            margin-bottom: 4px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .item-responsible {
            font-size: 10pt;
            color: #444;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .item-notes {
            font-size: 9pt;
            color: #888;
            font-style: italic;
            margin-top: 4px;
            padding: 4px 8px;
            background: #f5f5f5;
            border-radius: 4px;
            word-wrap: break-word;
            overflow-wrap: break-word;
            white-space: pre-wrap;
        }

        .assignments-section {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e5e5;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .assignments-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
        }

        .assignment-item {
            display: flex;
            align-items: center;
            padding: 6px 10px;
            background: #f9fafb;
            border-radius: 4px;
        }

        .assignment-position {
            font-weight: 500;
            margin-right: 8px;
        }

        .assignment-name {
            color: #666;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
            font-size: 9pt;
            color: #999;
            text-align: center;
        }

        .no-print {
            margin-bottom: 20px;
            text-align: center;
        }

        .print-btn {
            padding: 10px 20px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
        }

        .print-btn:hover {
            background: #2563eb;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">Друкувати</button>
    </div>

    <div class="header">
        <div class="church-name">{{ $event->church->name }}</div>
        <div class="event-title">{{ $event->title }}</div>
        <div class="event-date">{{ $event->date->translatedFormat('l, d F Y') }}</div>
        <div class="event-time">Початок: {{ $event->time->format('H:i') }}</div>
    </div>

    <div class="plan-items">
        @foreach($event->planItems as $item)
            <div class="plan-item">
                <div class="time-column">
                    <div class="time-start">{{ $item->start_time ? \Carbon\Carbon::parse($item->start_time)->format('H:i') : '--:--' }}</div>
                    @if($item->end_time)
                        <div class="time-end">{{ \Carbon\Carbon::parse($item->end_time)->format('H:i') }}</div>
                    @endif
                    @if($item->duration_minutes)
                        <div class="time-duration">{{ $item->formatted_duration }}</div>
                    @endif
                </div>

                <div class="divider" style="background-color: {{ $item->type_color }};"></div>

                <div class="content-column">
                    @if($item->type)
                        <div class="item-type" style="color: {{ $item->type_color }};">{{ $item->type_label }}</div>
                    @endif
                    <div class="item-title">{{ $item->title }}</div>

                    @if($item->description)
                        <div class="item-description">{{ $item->description }}</div>
                    @endif

                    @if($item->responsible_display)
                        <div class="item-responsible">
                            <strong>Відповідальний:</strong> {{ $item->responsible_display }}
                        </div>
                    @endif

                    @if($item->notes)
                        <div class="item-notes" style="white-space: pre-line;">{{ $item->notes }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if($event->assignments->isNotEmpty())
        <div class="assignments-section">
            <div class="section-title">Команда події</div>
            <div class="assignments-grid">
                @foreach($event->assignments as $assignment)
                    <div class="assignment-item">
                        <span class="assignment-position">{{ $assignment->position->name }}:</span>
                        <span class="assignment-name">{{ $assignment->person->full_name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="footer">
        Сформовано: {{ now()->format('d.m.Y H:i') }} | {{ $event->ministry->name }} | {{ $event->church->name }}
    </div>
</body>
</html>
