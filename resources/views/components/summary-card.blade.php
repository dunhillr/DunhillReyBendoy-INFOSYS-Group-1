@props(['title', 'value', 'color', 'icon' => '', 'change' => null])

{{-- 1. Outer Container: Keep white background and shadow --}}
<div class="col-md-4 mb-3">
    <div class="card shadow-sm h-100 p-0 overflow-hidden">
        
        <div class="card-body text-dark p-4">
            {{-- 2. Title & Icon Section (Top) --}}
            <h6 class="card-subtitle text-muted d-flex align-items-center mb-1">
                @if ($icon)<i class="fas fa-{{ $icon }} me-2"></i>@endif
                {{ $title }}
            </h6>

            {{-- 3. Value (Main Focus) --}}
            <h2 class="fw-bolder mb-2">{{ $value }}</h2>
            
            {{-- 4. MoM Percentage Change Indicator --}}
            @if (is_numeric($change))
                @php
                    $changeValue = abs($change);
                    
                    // Determine direction, color, and arrow
                    if ($change > 0.01) { 
                        // Green text/arrow for positive
                        $indicatorClass = 'text-success'; 
                        $indicatorArrow = '▲'; 
                    } elseif ($change < -0.01) { 
                        // Red text/arrow for negative
                        $indicatorClass = 'text-danger'; 
                        $indicatorArrow = '▼';
                    } else { 
                        // Muted color for no change
                        $indicatorClass = 'text-muted'; 
                        $indicatorArrow = '—'; 
                    }
                @endphp

                <p class="mb-0 fw-bold d-flex align-items-center">
                    {{-- Full Indicator Span: Arrow and Percentage Value --}}
                    <span class="{{ $indicatorClass }} me-1" style="font-size: 1.1em;">
                        {{ $indicatorArrow }}
                        {{ number_format($changeValue, 1) }}%
                    </span>
                    
                    <span class="ms-1 text-muted opacity-75">
                        vs Last Month
                    </span>
                </p>
            @endif
        </div>
        
        {{-- 5. Decorative Bottom Border --}}
        {{-- Use a separate div to create a solid line/border effect --}}
        <div class="bg-{{ $color }}" style="height: 6px; width: 100%;"></div>

    </div>
</div>