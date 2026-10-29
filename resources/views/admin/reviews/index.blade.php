@extends('admin.layouts.app')

@section('title', 'Product Reviews')

@section('content')
    <div class="page-heading">
        <h1>Product Reviews</h1>
        <p>Review customer feedback before it appears in the store.</p>
    </div>

    <section class="panel" style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse; text-align:left;">
            <thead>
                <tr>
                    <th style="padding:10px;">Product</th>
                    <th style="padding:10px;">Customer</th>
                    <th style="padding:10px;">Order</th>
                    <th style="padding:10px;">Rating</th>
                    <th style="padding:10px;">Review</th>
                    <th style="padding:10px;">Photo</th>
                    <th style="padding:10px;">Status</th>
                    <th style="padding:10px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reviews as $review)
                    <tr>
                        <td style="padding:10px;">{{ $review->product?->name ?? 'Deleted product' }}</td>
                        <td style="padding:10px;">{{ $review->user?->name ?? 'Deleted customer' }}</td>
                        <td style="padding:10px;">{{ $review->order?->order_number ?? 'Deleted order' }}</td>
                        <td style="padding:10px; white-space:nowrap;">{{ $review->rating }} / 5</td>
                        <td style="padding:10px; max-width:360px; overflow-wrap:anywhere;">{{ $review->review ?: 'No written comment' }}</td>
                        <td style="padding:10px;">
                            @if ($review->photo_path)
                                <a href="{{ asset('storage/' . $review->photo_path) }}" target="_blank" rel="noopener">View photo</a>
                            @else
                                No photo
                            @endif
                        </td>
                        <td style="padding:10px;">{{ $review->is_approved ? 'Published' : 'Pending' }}</td>
                        <td style="padding:10px;">
                            @unless ($review->is_approved)
                                <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="button" type="submit">Approve</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" style="padding:10px;">No reviews submitted yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $reviews->links() }}
    </section>
@endsection