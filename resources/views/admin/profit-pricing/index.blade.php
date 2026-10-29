@extends('admin.layouts.app')

@section('title', 'Profit & Pricing')

@section('content')
    <div class="page-heading">
        <h1>Profit &amp; Pricing</h1>
        <p>Review product costs, target profit percentages, and suggested selling prices.</p>
    </div>

    <div class="panel">
        <h2>Pricing Formula</h2>
        <p class="muted">
            Suggested selling price = Unit cost × (1 + Profit percentage ÷ 100).
        </p>
        <p class="muted">
            Example: ₱1,000 unit cost with a 10% target profit gives a suggested price of ₱1,100.
            This recommendation does not automatically change the current selling price.
        </p>
        <a class="button" href="{{ route('admin.dashboard') }}">
            Edit Product Costs and Pricing
        </a>
    </div>

    <div class="panel">
        <h2>Product Pricing Overview</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Unit</th>
                        <th>Current Selling Price</th>
                        <th>Unit Cost</th>
                        <th>Target Profit</th>
                        <th>Suggested Price</th>
                        <th>Estimated Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pricingRows as $row)
                        <tr>
                            <td>{{ $row['productName'] }}</td>
                            <td>{{ $row['unit'] }}</td>
                            <td>
                                @if ($row['currentPrice'] !== null)
                                    ₱{{ number_format((float) $row['currentPrice'], 2) }}
                                @else
                                    Not set
                                @endif
                            </td>
                            <td>
                                @if ($row['cost'] !== null)
                                    ₱{{ number_format((float) $row['cost'], 2) }}
                                @else
                                    Not recorded
                                @endif
                            </td>
                            <td>
                                @if ($row['profitPercentage'] !== null)
                                    {{ number_format((float) $row['profitPercentage'], 2) }}%
                                @else
                                    Not set
                                @endif
                            </td>
                            <td>
                                @if ($row['suggestedPrice'] !== null)
                                    ₱{{ number_format($row['suggestedPrice'], 2) }}
                                @else
                                    Not available
                                @endif
                            </td>
                            <td>
                                @if ($row['estimatedProfit'] !== null)
                                    ₱{{ number_format($row['estimatedProfit'], 2) }}
                                @else
                                    Not available
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="muted">
            Generic products are listed per active selling unit. Estimated profit is based
            on the configured unit cost and target percentage. It is not actual sales profit.
        </p>
    </div>
@endsection