 @extends('admin.layouts.app')

@section('content')
@if ($drawerMode)
<style>
    body.drawer-embedded .sidebar,
    body.drawer-embedded .admin-header,
    body.drawer-embedded .sidebar-overlay,
    body.drawer-embedded .notification-floating { display: none !important; }
    body.drawer-embedded .admin-main {
        width: 100% !important;
        min-height: 100dvh !important;
        margin: 0 !important;
        padding: 14px !important;
    }
    body.drawer-embedded .manual-transaction-page { max-width: none; }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => document.body.classList.add('drawer-embedded'));
</script>
@endif
<style>
.manual-transaction-page .card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 4px 18px rgba(15,23,42,.05);
    overflow: hidden;
}
.manual-transaction-page .card-header {
    border-bottom: 1px solid #edf0f4;
}
.manual-transaction-page .form-control,
.manual-transaction-page .form-select {
    min-height: 44px;
    border-color: #d6dce5;
    border-radius: 8px;
}
.manual-transaction-page #itemsContainer {
    display: grid;
    grid-template-columns: minmax(0,1fr);
    gap: 1rem;
}
.manual-transaction-page .transaction-item {
    margin: 0 !important;
    background: #fff !important;
    border: 1px solid #dce3ec !important;
    border-radius: 12px !important;
    box-shadow: 0 3px 12px rgba(15,23,42,.04);
}
.manual-transaction-page .transaction-item .line-total {
    color: #146c43;
}
.manual-transaction-page #grandTotal {
    color: #146c43;
    font-size: 2rem;
    font-weight: 800;
}
.manual-transaction-page #paymentSummary .border.rounded {
    min-height: 88px;
    padding: 1rem !important;
    border-color: #dfe5ec !important;
    border-radius: 10px !important;
    background: #f8fafc;
}
.manual-transaction-page #paymentSummary {
    border-color: #cfe5d8 !important;
}
@media (max-width: 575.98px) {
    .manual-transaction-page .card-body {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
}
/* Manual transaction layout fix */
.manual-transaction-page .transaction-item .row.g-3 {
    display: grid !important;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: 1rem !important;
    margin: 0 !important;
}

.manual-transaction-page .transaction-item .row.g-3 > [class*="col-"] {
    width: auto !important;
    max-width: none !important;
    min-width: 0;
    padding: 0 !important;
    grid-column: span 3;
}

.manual-transaction-page .transaction-item .row.g-3 > .col-lg-5 {
    grid-column: span 5;
}

.manual-transaction-page .transaction-item .row.g-3 > .col-lg-2 {
    grid-column: span 2;
}

.manual-transaction-page .transaction-item .row.g-3 > .col-lg-3 {
    grid-column: span 3;
}

.manual-transaction-page .transaction-item .row.g-3 > .col-12 {
    grid-column: 1 / -1;
}

.manual-transaction-page .transaction-item .form-label {
    display: block;
    margin-bottom: .5rem;
}

.manual-transaction-page .transaction-item .form-control,
.manual-transaction-page .transaction-item .form-select {
    display: block;
    width: 100%;
    box-sizing: border-box;
}

.manual-transaction-page #paymentSummary .row.g-3 {
    display: grid !important;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem !important;
    margin: 0 !important;
}

.manual-transaction-page #paymentSummary .row.g-3 > [class*="col-"] {
    width: auto !important;
    max-width: none !important;
    min-width: 0;
    padding: 0 !important;
}

.manual-transaction-page #paymentSummary .payment-metric,
.manual-transaction-page #paymentSummary .border.rounded {
    box-sizing: border-box;
    min-width: 0;
    height: 100%;
    overflow-wrap: anywhere;
}

.manual-transaction-page #paymentSummary input {
    width: 100%;
    box-sizing: border-box;
}

@media (max-width: 991.98px) {
    .manual-transaction-page .transaction-item .row.g-3 > [class*="col-"] {
        grid-column: span 6;
    }

    .manual-transaction-page .transaction-item .row.g-3 > .col-lg-5,
    .manual-transaction-page .transaction-item .row.g-3 > .col-lg-3,
    .manual-transaction-page .transaction-item .row.g-3 > .col-12 {
        grid-column: 1 / -1;
    }

    .manual-transaction-page #paymentSummary .row.g-3 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .manual-transaction-page #cashPaymentFields {
        grid-column: 1 / -1;
    }
}

@media (max-width: 575.98px) {
    .manual-transaction-page .transaction-item .row.g-3 {
        grid-template-columns: minmax(0, 1fr);
    }

    .manual-transaction-page .transaction-item .row.g-3 > [class*="col-"],
    .manual-transaction-page .transaction-item .row.g-3 > .col-lg-5,
    .manual-transaction-page .transaction-item .row.g-3 > .col-lg-2,
    .manual-transaction-page .transaction-item .row.g-3 > .col-lg-3,
    .manual-transaction-page .transaction-item .row.g-3 > .col-12 {
        grid-column: 1 / -1;
    }

    .manual-transaction-page #paymentSummary .row.g-3 {
        grid-template-columns: minmax(0, 1fr);
    }

    .manual-transaction-page #cashPaymentFields {
        grid-column: 1;
    }
}
/* Manual transaction visual polish */
.manual-transaction-page .card-header {
    padding: 1rem 1.25rem;
    font-size: 1rem;
    font-weight: 700;
}

.manual-transaction-page #itemsContainer {
    gap: 1.25rem;
    margin-top: 1rem;
}

.manual-transaction-page .transaction-item {
    padding: 1.25rem !important;
    border-color: #d9e2ec !important;
    border-radius: 14px !important;
    box-shadow: 0 4px 14px rgba(15, 23, 42, .06);
}

.manual-transaction-page .transaction-item > .d-flex:first-child {
    padding-bottom: .9rem;
    margin-bottom: 1.1rem !important;
    border-bottom: 1px solid #e8edf3;
    gap: 1rem;
}

.manual-transaction-page .transaction-item > .d-flex:first-child h6 {
    font-size: 1rem;
    font-weight: 700;
    color: #234d35;
}

.manual-transaction-page .transaction-item .remove-item {
    flex-shrink: 0;
    border-radius: 7px !important;
}

.manual-transaction-page .transaction-item .line-total {
    font-size: 1.15rem !important;
    font-weight: 800;
    color: #176b43;
}

.manual-transaction-page .transaction-item .row.g-3 > .col-12 {
    padding-top: .9rem !important;
    border-top: 1px solid #e8edf3;
}

.manual-transaction-page #itemsContainer .form-label {
    font-size: .875rem;
    font-weight: 600;
    color: #334155;
}

.manual-transaction-page #grandTotal {
    display: inline-block;
    margin-top: .25rem;
    color: #176b43;
    font-size: 2.15rem;
    font-weight: 800;
    line-height: 1.25;
    letter-spacing: -.025em;
}

.manual-transaction-page .d-flex.justify-content-end.mt-4 > div {
    min-width: 280px !important;
    padding: 1.25rem 1.5rem !important;
    border: 1px solid #cfe3d5 !important;
    border-radius: 14px !important;
    background: #f0f8f2;
    box-shadow: 0 4px 14px rgba(22, 107, 67, .07);
}

.manual-transaction-page #paymentSummary {
    margin-top: 1.25rem !important;
    border: 1px solid #cfe3d5 !important;
    border-radius: 14px !important;
    box-shadow: 0 4px 16px rgba(15, 23, 42, .05);
}

.manual-transaction-page #paymentSummary > .card-header {
    background: #f5faf6 !important;
    color: #234d35;
}

.manual-transaction-page #cashPaymentFields {
    padding: 1rem;
    border: 1px solid #dfe7e1;
    border-radius: 10px;
    background: #fff;
}

.manual-transaction-page #paymentSummary .row.mt-3.g-3 {
    margin-top: 1rem !important;
}

.manual-transaction-page #paymentSummary .border.rounded {
    min-height: 105px;
    padding: 1.1rem !important;
    border: 1px solid #dfe5ec !important;
    border-radius: 10px !important;
    background: #f8fafc;
}

.manual-transaction-page #changeAmount,
.manual-transaction-page #remainingBalance {
    margin-top: .45rem;
    font-size: 1.35rem;
    font-weight: 800;
}

.manual-transaction-page #paymentMessage {
    padding: .75rem .9rem;
    border-radius: 8px;
    background: #f8fafc;
    line-height: 1.5;
}

.manual-transaction-page #addItem {
    margin-bottom: .75rem;
    padding: .7rem 1.1rem;
    border-radius: 8px;
    font-weight: 700;
}

.manual-transaction-page .card-body > p {
    line-height: 1.6;
}

@media (max-width: 575.98px) {
    .manual-transaction-page .transaction-item {
        padding: 1rem !important;
    }

    .manual-transaction-page .d-flex.justify-content-end.mt-4 > div {
        width: 100%;
        min-width: 0 !important;
    }

    .manual-transaction-page #grandTotal {
        font-size: 1.8rem;
    }
}
/* Manual transaction compact mode */
.manual-transaction-page {
    padding-top: .5rem !important;
    padding-bottom: .5rem !important;
}

.manual-transaction-page .card {
    margin-bottom: .65rem !important;
    border-radius: 10px !important;
}

.manual-transaction-page .card-header {
    padding: .55rem .9rem !important;
    font-size: .92rem !important;
}

.manual-transaction-page .card-body {
    padding: .75rem .9rem !important;
}

.manual-transaction-page .mb-3 {
    margin-bottom: .6rem !important;
}

.manual-transaction-page .mt-4 {
    margin-top: .65rem !important;
}

.manual-transaction-page .transaction-item {
    padding: .8rem !important;
    margin-bottom: .65rem !important;
    border-radius: 9px !important;
}

.manual-transaction-page .transaction-item > .d-flex:first-child {
    padding-bottom: .45rem !important;
    margin-bottom: .6rem !important;
}

.manual-transaction-page .transaction-item .row.g-3 {
    gap: .5rem !important;
}

.manual-transaction-page .form-label {
    margin-bottom: .25rem !important;
    font-size: .8rem !important;
}

.manual-transaction-page .form-control,
.manual-transaction-page .form-select {
    min-height: 36px !important;
    padding: .35rem .6rem !important;
    font-size: .9rem !important;
}

.manual-transaction-page .transaction-item .row.g-3 > .col-12 {
    padding-top: .45rem !important;
}

.manual-transaction-page #addItem {
    margin-bottom: .35rem !important;
    padding: .45rem .8rem !important;
}

.manual-transaction-page #itemsContainer {
    gap: .55rem !important;
    margin-top: .55rem !important;
}

.manual-transaction-page .d-flex.justify-content-end.mt-4 > div {
    min-width: 220px !important;
    padding: .65rem 1rem !important;
    border-radius: 10px !important;
}

.manual-transaction-page #grandTotal {
    margin-top: 0 !important;
    font-size: 1.65rem !important;
}

.manual-transaction-page #paymentSummary {
    margin-top: .65rem !important;
}

.manual-transaction-page #paymentSummary .card-body {
    padding: .65rem .8rem !important;
}

.manual-transaction-page #cashPaymentFields {
    padding: .55rem .7rem !important;
}

.manual-transaction-page #paymentSummary .row.mt-3.g-3 {
    margin-top: .5rem !important;
    gap: .5rem !important;
}

.manual-transaction-page #paymentSummary .border.rounded {
    min-height: 68px !important;
    padding: .6rem .75rem !important;
}

.manual-transaction-page #changeAmount,
.manual-transaction-page #remainingBalance {
    margin-top: .15rem !important;
    font-size: 1.08rem !important;
}

.manual-transaction-page #paymentMessage {
    margin-top: .5rem !important;
    padding: .4rem .65rem !important;
}

.manual-transaction-page .transaction-item .line-total {
    font-size: 1rem !important;
}

.manual-transaction-page .card-body p {
    margin-bottom: .45rem !important;
}

@media (min-width: 992px) {
    .manual-transaction-page #paymentSummary .card-body {
        display: grid;
        grid-template-columns: minmax(220px, .9fr) minmax(0, 2fr);
        gap: .65rem 1rem;
        align-items: start;
    }

    .manual-transaction-page #paymentSummary #cashPaymentFields {
        grid-column: 1;
    }

    .manual-transaction-page #paymentSummary .row.mt-3.g-3 {
        grid-column: 2;
        grid-row: 1;
        margin-top: 0 !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }

    .manual-transaction-page #paymentSummary #paymentMessage {
        grid-column: 1 / -1;
    }
}

@media (max-width: 575.98px) {
    .manual-transaction-page .transaction-item {
        padding: .65rem !important;
    }

    .manual-transaction-page .d-flex.justify-content-end.mt-4 > div {
        width: 100%;
        min-width: 0 !important;
    }

    .manual-transaction-page #grandTotal {
        font-size: 1.45rem !important;
    }
}
/* Tiny product item card */
.manual-transaction-page #itemsContainer {
    gap: .4rem !important;
    margin-top: .35rem !important;
}

.manual-transaction-page .transaction-item {
    padding: .5rem .65rem !important;
    margin-bottom: .35rem !important;
    border-radius: 7px !important;
    box-shadow: none !important;
}

.manual-transaction-page .transaction-item > .d-flex:first-child {
    padding-bottom: .3rem !important;
    margin-bottom: .4rem !important;
    border-bottom: 1px solid #edf0f4 !important;
}

.manual-transaction-page .transaction-item > .d-flex:first-child h6 {
    font-size: .84rem !important;
}

.manual-transaction-page .transaction-item .remove-item {
    padding: 3px 8px !important;
    font-size: .75rem !important;
    line-height: 1.3 !important;
}

.manual-transaction-page .transaction-item .row.g-3 {
    gap: .35rem !important;
}

.manual-transaction-page .transaction-item .row.g-3 > [class*="col-"] {
    min-width: 0 !important;
}

.manual-transaction-page .transaction-item .form-label {
    margin-bottom: .15rem !important;
    font-size: .74rem !important;
    line-height: 1.25 !important;
}

.manual-transaction-page .transaction-item .form-control,
.manual-transaction-page .transaction-item .form-select {
    min-height: 31px !important;
    height: 31px;
    padding: .2rem .4rem !important;
    font-size: .8rem !important;
    border-radius: 5px !important;
}

.manual-transaction-page .transaction-item select.form-select {
    padding-right: 1.5rem !important;
}

.manual-transaction-page .transaction-item .row.g-3 > .col-12 {
    padding-top: .3rem !important;
    margin-top: .1rem !important;
}

.manual-transaction-page .transaction-item .line-total {
    font-size: .9rem !important;
    line-height: 1.3 !important;
}

.manual-transaction-page .transaction-item .text-muted,
.manual-transaction-page .transaction-item small,
.manual-transaction-page .transaction-item .form-text {
    font-size: .7rem !important;
    line-height: 1.25 !important;
}

.manual-transaction-page .transaction-item .quantity-hint,
.manual-transaction-page .transaction-item .stock-hint {
    margin-top: .15rem !important;
}

@media (min-width: 992px) {
    .manual-transaction-page .transaction-item .row.g-3 {
        grid-template-columns: minmax(0, 2.5fr) minmax(105px, 1fr) minmax(125px, 1fr) minmax(145px, 1.2fr) !important;
        align-items: start;
    }

    .manual-transaction-page .transaction-item .row.g-3 > .col-lg-5,
    .manual-transaction-page .transaction-item .row.g-3 > .col-lg-2,
    .manual-transaction-page .transaction-item .row.g-3 > .col-lg-3 {
        grid-column: auto !important;
    }

    .manual-transaction-page .transaction-item .row.g-3 > .col-12 {
        grid-column: 1 / -1 !important;
    }
}

.manual-transaction-page {
    max-width: 1800px;
    margin-inline: auto;
}

.manual-transaction-page > .d-flex:first-child {
    padding: 18px 22px;
    margin-bottom: 18px !important;
    border: 1px solid #e1e9e2;
    border-radius: 14px;
    background: linear-gradient(110deg, #fff 0%, #f3f8f3 100%);
}

.pos-register-layout {
    display: grid;
    grid-template-columns: minmax(0, 1.12fr) minmax(430px, .88fr);
    gap: 18px;
    align-items: start;
}

.pos-product-panel,
.pos-checkout-panel > .card {
    border: 1px solid #e0e8e1 !important;
    border-radius: 14px !important;
    background: #fff;
    box-shadow: 0 8px 26px rgba(27, 55, 34, .06) !important;
}

.pos-product-panel {
    position: sticky;
    top: 18px;
    padding: 20px;
}

.pos-panel-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 18px;
}

.pos-panel-heading h2 {
    margin: 4px 0;
    color: #1c3e29;
    font-size: 1.25rem;
    font-weight: 800;
}

.pos-panel-heading p {
    margin: 0;
    color: #69786d;
    font-size: .86rem;
}

.pos-eyebrow {
    color: #64816b;
    font-size: .67rem;
    font-weight: 800;
    letter-spacing: .12em;
}

.pos-product-count {
    flex-shrink: 0;
    padding: 6px 9px;
    border-radius: 999px;
    background: #edf5ee;
    color: #275b37;
    font-size: .75rem;
    font-weight: 700;
}

.pos-search {
    display: flex;
    align-items: center;
    gap: 10px;
    min-height: 48px;
    padding: 0 12px;
    border: 1px solid #cfdacf;
    border-radius: 10px;
    background: #fff;
}

.pos-search > span {
    color: #54745c;
    font-size: 1.4rem;
    line-height: 1;
}

.pos-search input {
    width: 100%;
    min-width: 0;
    border: 0;
    outline: 0;
    background: transparent;
}

.pos-search kbd {
    padding: 2px 6px;
    border: 1px solid #dce4dc;
    border-radius: 5px;
    color: #748176;
    font-size: .7rem;
}

.pos-category-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 14px 0;
}

.pos-category-filters button {
    padding: 7px 12px;
    border: 1px solid #dbe5dc;
    border-radius: 999px;
    background: #fff;
    color: #415447;
    font-size: .78rem;
    font-weight: 700;
}

.pos-category-filters button.active {
    border-color: #245c35;
    background: #245c35;
    color: #fff;
}

.pos-product-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    max-height: min(68vh, 760px);
    overflow-y: auto;
    padding: 2px 4px 2px 2px;
}

.pos-product-tile {
    display: flex;
    min-height: 124px;
    flex-direction: column;
    align-items: flex-start;
    justify-content: space-between;
    padding: 12px;
    border: 1px solid #e0e8e1;
    border-radius: 11px;
    background: #fff;
    color: #25392b;
    text-align: left;
    transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease;
}

.pos-product-tile:hover:not(:disabled),
.pos-product-tile:focus-visible {
    transform: translateY(-2px);
    border-color: #72a27b;
    box-shadow: 0 6px 16px rgba(27, 82, 43, .1);
    outline: 0;
}

.pos-product-tile:disabled {
    cursor: not-allowed;
    opacity: .52;
}

.pos-product-tile strong {
    display: -webkit-box;
    overflow: hidden;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    font-size: .86rem;
    line-height: 1.3;
}

.pos-product-tile .pos-tile-image {
    display: block;
    width: 100%;
    height: 104px;
    margin-bottom: 10px;
    border-radius: 8px;
    background: #f3f6f2;
    object-fit: contain;
}

.pos-product-tile .pos-tile-description {
    width: 100%;
}

.pos-product-tile .pos-tile-type {
    margin-top: 5px;
    color: #758278;
    font-size: .68rem;
}

.pos-product-tile .pos-tile-bottom {
    display: flex;
    width: 100%;
    justify-content: space-between;
    align-items: flex-end;
    gap: 6px;
    margin-top: 10px;
}

.pos-product-tile .pos-tile-price {
    color: #17653a;
    font-size: .84rem;
    font-weight: 800;
}

.pos-product-tile .pos-tile-stock {
    color: #758278;
    font-size: .67rem;
    text-align: right;
}

.pos-product-tile .pos-tile-stock.out {
    color: #b42318;
    font-weight: 700;
}

.pos-empty-products {
    padding: 30px 12px;
    color: #758278;
    text-align: center;
}

.pos-checkout-panel {
    display: grid;
    gap: 14px;
    min-width: 0;
}

.pos-checkout-panel > .card {
    margin-bottom: 0 !important;
}

.pos-checkout-panel > .card > .card-header {
    padding: 16px 18px 6px !important;
}

.pos-checkout-panel > .card > .card-body {
    padding: 12px 18px 18px !important;
}

.pos-cart-tip {
    margin: 2px 0 12px;
    padding: 9px 11px;
    border-radius: 8px;
    background: #f2f7f2;
    color: #4a6550;
    font-size: .76rem;
}

.pos-checkout-panel #itemsContainer {
    max-height: 43vh;
    overflow: auto;
    padding-right: 3px;
}

.pos-checkout-panel .transaction-item {
    border-color: #e2e9e3 !important;
    background: #fff !important;
}

.pos-checkout-panel #grandTotal {
    color: #174b2c;
}

.pos-checkout-panel #paymentSummary {
    margin-top: 14px !important;
    background: #f7fbf7;
}

.pos-checkout-panel #paymentSummary .card-header {
    padding: 12px 14px !important;
}

.pos-checkout-panel #paymentSummary .card-body {
    padding: 12px !important;
}

.pos-checkout-panel .pos-customer-card .row {
    row-gap: 10px !important;
}

.pos-checkout-panel .pos-customer-card .card-body {
    padding-top: 8px !important;
}

.manual-transaction-page > .alert-warning {
    margin-top: 14px;
}

@media (max-width: 1050px) {
    .pos-register-layout {
        grid-template-columns: minmax(0, 1fr);
    }

    .pos-product-panel {
        position: static;
    }

    .pos-product-grid {
        max-height: 390px;
    }
}

@media (max-width: 700px) {
    .pos-product-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        max-height: 340px;
    }

    .pos-product-panel {
        padding: 14px;
    }
}
</style>

<div class="container-fluid py-3 manual-transaction-page">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">{{ $defaultPaymentMethod === 'utang' ? 'Record Product Utang' : 'New Manual Transaction' }}</h1>
            <p class="text-muted mb-0">Record a walk-in sale with live pricing, stock checks, and payment totals.</p>
        </div>

        @unless ($drawerMode)
        <a href="{{ route('admin.sales.index') }}"
           style="display:inline-block;background:#6c757d;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;font-weight:600;border:1px solid #6c757d;">
            ← Back to Sales
        </a>
        @endunless
    </div>

    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm" role="alert">
            <strong>Please review the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (!$shift && $defaultPaymentMethod !== 'utang')
        <div class="alert alert-warning shadow-sm" role="status">
            Open your cash drawer shift on the
            <a href="{{ route('admin.sales.index') }}">Sales (Benta) page</a>
            before saving Cash or GCash sales. Choose Utang to record a debt without a cash payment.
        </div>
    @elseif ($defaultPaymentMethod === 'utang')
        <div class="alert alert-info shadow-sm" role="status">
            Record the customer and products. No cash payment is required now; the full total will be tracked as a balance.
        </div>
    @endif

    @if ($products->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <h5 class="fw-bold">No Active Products Available</h5>
                <p class="text-muted">
                    Activate or add products in Product Management before creating a transaction.
                </p>
                <a href="{{ route('admin.manual-transactions.index') }}"
                   style="display:inline-block;background:#fff;color:#495057;padding:12px 24px;border:1px solid #6c757d;border-radius:6px;text-decoration:none;font-weight:600;">
                    Cancel
                </a>
            </div>
        </div>
    @else

    <form method="POST"
          action="{{ route('admin.manual-transactions.store') }}"
          id="manualTransactionForm">
        @csrf

        <div class="pos-register-layout">
            <section class="pos-product-panel">
                <div class="pos-panel-heading">
                    <div>
                        <span class="pos-eyebrow">PRODUCT CATALOG</span>
                        <h2>Choose products</h2>
                        <p>Search or tap a product to add it to the cart.</p>
                    </div>
                    <span class="pos-product-count">{{ $products->count() }} active</span>
                </div>

                <label class="pos-search">
                    <span aria-hidden="true">⌕</span>
                    <input type="search" id="posProductSearch"
                           placeholder="Search products by name or brand"
                           autocomplete="off" aria-label="Search products">
                    <kbd>/</kbd>
                </label>

                <div class="pos-category-filters" role="group" aria-label="Filter products">
                    <button type="button" class="active" data-pos-filter="all">All products</button>
                    <button type="button" data-pos-filter="rice">Rice</button>
                    <button type="button" data-pos-filter="generic">General goods</button>
                </div>

                <div id="posProductGrid" class="pos-product-grid"></div>
                <p id="posNoProducts" class="pos-empty-products" hidden>
                    No matching products. Try a different search.
                </p>
            </section>

            <div class="pos-checkout-panel">
        {{-- Customer Information --}}
        <div class="card border-0 shadow-sm mb-4 pos-customer-card">
            <div class="card-header bg-white border-0 px-4 pt-4">
                <h5 class="fw-bold mb-1">Customer Information</h5>
                <p class="text-muted small mb-0">
                    Enter the customer details and payment method.
                </p>
            </div>

            <div class="card-body px-4 py-3">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="customer_name" class="form-label fw-semibold">
                            Customer Name <span id="customerNameRequired" class="text-muted">(Optional)</span>
                        </label>
                           <input type="text" name="customer_name" id="customer_name"
                               class="form-control" maxlength="255"
                               value="{{ old('customer_name') }}"
                               @required($defaultPaymentMethod === 'utang')
                               placeholder="Enter customer name">
                    </div>

                    <div class="col-md-6">
                        <label for="customer_phone" class="form-label fw-semibold">
                            Phone Number (Optional)
                        </label>
                        <input type="text" name="customer_phone" id="customer_phone"
                               class="form-control" maxlength="30"
                               value="{{ old('customer_phone') }}"
                               placeholder="Enter phone number">
                    </div>

                    <div class="col-md-6">
                        <label for="payment_method" class="form-label fw-semibold">
                            Payment Method <span class="text-danger">*</span>
                        </label>
                        <select name="payment_method" id="payment_method"
                                class="form-select" required>
                            <option value="cash"
                                {{ old('payment_method', $defaultPaymentMethod) === 'cash' ? 'selected' : '' }}>
                                Cash
                            </option>
                            <option value="gcash"
                                {{ old('payment_method', $defaultPaymentMethod) === 'gcash' ? 'selected' : '' }}>
                                GCash
                            </option>
                            <option value="utang"
                                {{ old('payment_method', $defaultPaymentMethod) === 'utang' ? 'selected' : '' }}>
                                Utang
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6" id="gcashReferenceField" style="display: none;">
                        <label for="gcash_reference_number" class="form-label fw-semibold">
                            GCash Reference Number <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            name="gcash_reference_number"
                            id="gcash_reference_number"
                            class="form-control"
                            maxlength="100"
                            value="{{ old('gcash_reference_number') }}"
                            placeholder="Enter GCash reference number"
                        >
                        @error('gcash_reference_number')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="notes" class="form-label fw-semibold">
                            Transaction Notes <span class="text-muted fw-normal">(Optional)</span>
                        </label>
                        <input type="text" name="notes" id="notes"
                               class="form-control" maxlength="1000"
                               value="{{ old('notes') }}"
                               placeholder="Discount details or pickup instructions">
                    </div>
                </div>
            </div>
        </div>

        {{-- Order Items --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 px-4 pt-4 pb-3">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <span class="pos-eyebrow">CURRENT SALE</span>
                        <h5 class="fw-bold mb-1">Shopping cart</h5>
                        <p class="text-muted small mb-0"><span id="posCartCount">0</span> items in this transaction</p>
                    </div>

                    <button type="button"
                            id="addItemButton"
                            style="background:#198754;color:#fff;padding:10px 18px;border:1px solid #198754;border-radius:6px;font-weight:600;cursor:pointer;">
                        + Add Product
                    </button>
                </div>
            </div>

            <div class="card-body px-4 pt-2 pb-4">
                <div class="pos-cart-tip">Prices and stock are checked automatically. Adjust unit and quantity for each item.</div>

                <div id="itemsContainer"></div>

                <div class="d-flex justify-content-end mt-4">
                    <div class="border rounded p-3 text-end" style="min-width: 240px;">
                        <div class="text-muted small mb-1">Transaction Total</div>
                        <div class="h3 fw-bold mb-0" id="grandTotal">₱0.00</div>
                    </div>
                </div>
                <div class="card mt-4 border-success" id="paymentSummary">
                    <div class="card-header bg-white fw-bold">Payment Summary</div>
                    <div class="card-body">
                        <div id="cashPaymentFields" style="display: {{ old('payment_method', $defaultPaymentMethod) === 'cash' ? 'block' : 'none' }};">
                            <label for="cash_received" class="form-label fw-semibold">
                                Cash Received (PHP)
                            </label>
                            <input type="number" name="cash_received" id="cash_received"
                                   class="form-control form-control-lg"
                                   min="0" max="100000000" step="0.01"
                                   value="{{ old('cash_received', '') }}"
                                   placeholder="Enter amount received" inputmode="decimal">
                        </div>

                        <div class="row mt-3 g-3">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="text-muted small">Change</div>
                                    <div class="h4 fw-bold text-success mb-0" id="changeAmount">PHP 0.00</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="text-muted small">Remaining Balance</div>
                                    <div class="h4 fw-bold text-danger mb-0" id="remainingBalance">PHP 0.00</div>
                                </div>
                            </div>
                        </div>
                        <div id="paymentMessage" class="small mt-3" role="status" aria-live="polite">
                            Enter the cash received to calculate change.
                        </div>
                    </div>
                </div>
            </div>
        </div>
            </div>
        </div>

        {{-- Inventory Notice --}}
        <div class="alert alert-warning small mb-4">
            <strong>Before saving:</strong>
            Review all items, quantities, and prices. Saving this transaction records the sale and deducts the sold quantities from inventory.
        </div>

        {{-- Action Buttons --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <a href="{{ route('admin.manual-transactions.index') }}"
               style="display:inline-block;background:#fff;color:#495057;padding:12px 24px;border:1px solid #6c757d;border-radius:6px;text-decoration:none;font-weight:600;">
                Cancel
            </a>

            <button type="submit"
                    id="submitTransactionButton"
                    data-has-shift="{{ $shift ? '1' : '0' }}"
                    @disabled(!$shift && $defaultPaymentMethod !== 'utang')
                    style="background:#198754;color:#fff;padding:12px 24px;border:1px solid #198754;border-radius:6px;font-weight:700;cursor:pointer;">
                {{ $defaultPaymentMethod === 'utang' ? 'Record Utang' : ($shift ? 'Save Transaction' : 'Open Cash Drawer to Save') }}
            </button>
        </div>
    </form>
    @endif
</div>

@if ($products->isNotEmpty())
<script>
document.addEventListener('DOMContentLoaded', function () {
    const products = @json($productData);
    const defaultProductImage = @json(asset('images/default-product.svg'));
    const oldItems = @json(old('items', []));
    const container = document.getElementById('itemsContainer');
    const addButton = document.getElementById('addItemButton');
    const totalDisplay = document.getElementById('grandTotal');
    const form = document.getElementById('manualTransactionForm');
    const submitButton = document.getElementById('submitTransactionButton');
    const paymentMethod = document.getElementById('payment_method');
    const customerNameInput = document.getElementById('customer_name');
    const customerNameRequired = document.getElementById('customerNameRequired');
    const gcashReferenceField = document.getElementById('gcashReferenceField');
    const gcashReferenceInput = document.getElementById('gcash_reference_number');
    const cashReceivedInput = document.getElementById('cash_received');
    const cashPaymentFields = document.getElementById('cashPaymentFields');
    const changeAmount = document.getElementById('changeAmount');
    const remainingBalance = document.getElementById('remainingBalance');
    const paymentMessage = document.getElementById('paymentMessage');

    let currentGrandTotal = 0;
    let nextIndex = 0;

    function peso(amount) {
        return '₱' + Number(amount || 0).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, char => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        })[char]);
    }

    function getProduct(id) {
        return products.find(p => String(p.id) === String(id));
    }

    function isGeneric(product) {
        return product?.inventory_type === 'generic';
    }

    function getUnit(product) {
        return isGeneric(product)
            ? (product.stock_unit || 'piece')
            : 'kg';
    }

    function getStock(product) {
        return Number(product?.stock_quantity ?? product?.stock_kg ?? 0);
    }

    function getSellingUnit(product, unit) {
        const normalizedUnit = String(unit || '').trim().toLowerCase();

        return (product?.selling_units || []).find(sellingUnit =>
            String(sellingUnit.unit_name || '').trim().toLowerCase() === normalizedUnit
        );
    }

    function getPrice(product, unit) {
        if (!product) return 0;

        if (isGeneric(product)) {
            return Number(getSellingUnit(product, unit)?.selling_price || 0);
        }

        return Number(
            unit === 'sack'
                ? product.price_per_sack
                : product.price_per_kg
        ) || 0;
    }

    const productGrid = document.getElementById('posProductGrid');
    const productSearch = document.getElementById('posProductSearch');
    const noProductsMessage = document.getElementById('posNoProducts');
    let activeProductFilter = 'all';

    function renderProductCatalog() {
        const query = productSearch.value.trim().toLowerCase();
        const filteredProducts = products.filter(product => {
            const matchesSearch = [
                product.name,
                product.brand,
                product.inventory_type,
                product.rice_type
            ].some(value => String(value || '').toLowerCase().includes(query));
            const matchesFilter = activeProductFilter === 'all'
                || product.inventory_type === activeProductFilter;

            return matchesSearch && matchesFilter;
        });

        productGrid.innerHTML = filteredProducts.map(product => {
            const generic = isGeneric(product);
            const sellingUnit = generic ? product.selling_units?.[0] : null;
            const price = generic
                ? Number(sellingUnit?.selling_price || 0)
                : Number(product.price_per_kg || product.price_per_sack || 0);
            const priceUnit = generic ? (sellingUnit?.unit_name || '') : 'kg';
            const stock = getStock(product);
            const outOfStock = !Number.isFinite(stock)
                || stock <= 0
                || (generic && !sellingUnit);
            const type = generic
                ? 'General goods'
                : (product.rice_type || 'Rice');

            return `
                <button type="button" class="pos-product-tile"
                    data-pos-product="${product.id}"
                    aria-label="Add ${escapeHtml(product.name)} to cart"
                    ${outOfStock ? 'disabled' : ''}>
                    <img class="pos-tile-image"
                         src="${escapeHtml(product.image_url || '')}"
                         alt="${escapeHtml(product.name)}"
                         loading="lazy"
                         onerror="this.onerror=null;this.src='${escapeHtml(defaultProductImage)}'">
                    <span class="pos-tile-description">
                        <strong>${escapeHtml(product.name)}</strong>
                        <span class="pos-tile-type">${escapeHtml(type)}</span>
                    </span>
                    <span class="pos-tile-bottom">
                        <span class="pos-tile-price">${price ? peso(price) + (priceUnit ? ' / ' + escapeHtml(priceUnit) : '') : 'Price unavailable'}</span>
                        <span class="pos-tile-stock ${outOfStock ? 'out' : ''}">
                            ${outOfStock ? 'Out of stock' : `${stock.toLocaleString()} ${escapeHtml(getUnit(product))}`}
                        </span>
                    </span>
                </button>
            `;
        }).join('');

        noProductsMessage.hidden = filteredProducts.length > 0;
    }

    productSearch.addEventListener('input', renderProductCatalog);
    document.querySelectorAll('[data-pos-filter]').forEach(button => {
        button.addEventListener('click', function () {
            activeProductFilter = button.dataset.posFilter;
            document.querySelectorAll('[data-pos-filter]').forEach(filterButton => {
                filterButton.classList.toggle('active', filterButton === button);
            });
            renderProductCatalog();
        });
    });

    productGrid.addEventListener('click', function (event) {
        const tile = event.target.closest('[data-pos-product]');

        if (tile) {
            addProductToCart(tile.dataset.posProduct);
        }
    });

    document.addEventListener('keydown', function (event) {
        if (
            event.key === '/'
            && !['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)
        ) {
            event.preventDefault();
            productSearch.focus();
        }
    });

    function updateGcashReferenceField() {
        const isGcash = paymentMethod.value === 'gcash';

        gcashReferenceField.style.display = isGcash ? '' : 'none';
        gcashReferenceInput.required = isGcash;
    }

    function productOptions(selectedId = '') {
        return products.map(product => {
            const generic = isGeneric(product);
            const unit = getUnit(product);
            const stock = getStock(product);

            return `
                <option value="${product.id}"
                    ${String(product.id) === String(selectedId) ? 'selected' : ''}>
                    ${escapeHtml(product.name)}
                    ${product.brand ? ' — ' + escapeHtml(product.brand) : ''}
                    (Stock: ${stock} ${escapeHtml(unit)})
                </option>
            `;
        }).join('');
    }

    function addItem(old = null, selectedProductId = '') {
        const index = nextIndex++;
        const row = document.createElement('div');

        row.className = 'transaction-item border rounded-3 p-3 p-md-4 mb-3 bg-light';
        row.dataset.index = index;

        row.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Product Item #${index + 1}</h6>
                <button type="button" class="remove-item"
                    style="background:#dc3545;color:#fff;padding:6px 12px;border:1px solid #dc3545;border-radius:5px;font-weight:600;cursor:pointer;">
                    Remove
                </button>
            </div>

            <div class="row g-3">
                <div class="col-lg-5">
                    <label class="form-label fw-semibold">Product <span class="text-danger">*</span></label>
                    <select name="items[${index}][product_id]"
                        class="form-select product-select" required>
                        <option value="">Choose a product</option>
                        ${productOptions(old?.product_id ?? '')}
                    </select>
                    <small class="text-muted stock-hint d-block mt-1"></small>
                </div>

                <div class="col-sm-4 col-lg-2">
                    <label class="form-label fw-semibold">Unit <span class="text-danger">*</span></label>
                    <select name="items[${index}][unit_type]"
                        class="form-select unit-select" required>
                    </select>
                </div>

                <div class="col-sm-4 col-lg-2">
                    <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                    <select class="form-select quantity-preset mb-2"
                        aria-label="Choose a quantity preset"></select>
                    <input type="number"
                        name="items[${index}][quantity]"
                        class="form-control quantity-input"
                        min="0.01" max="100000" step="0.01"
                        value="${escapeHtml(old?.quantity ?? 1)}" required>
                    <small class="text-muted quantity-unit-hint d-block mt-1"></small>
                    <small class="quantity-equivalent d-block mt-1 fw-semibold text-primary"></small>
                </div>

                <div class="col-sm-4 col-lg-3">
                    <label class="form-label fw-semibold">Unit Price (₱) <span class="text-danger">*</span></label>
                    <input type="number"
                        name="items[${index}][unit_price]"
                        class="form-control price-input"
                        min="0" max="1000000" step="0.01"
                        value="${escapeHtml(old?.unit_price ?? '')}"
                        readonly required>
                    <small class="text-muted default-price-hint d-block mt-1"></small>
                </div>

                <div class="col-12">
                    <div class="d-flex justify-content-end align-items-center border-top pt-3">
                        <span class="text-muted me-2">Line Total:</span>
                        <strong class="line-total fs-5">${peso(0)}</strong>
                    </div>
                </div>
            </div>
        `;

        container.appendChild(row);

        const productSelect = row.querySelector('.product-select');
        const unitSelect = row.querySelector('.unit-select');
        const quantityInput = row.querySelector('.quantity-input');
        const preset = row.querySelector('.quantity-preset');

        productSelect.addEventListener('change', function () {
            updateProductFields(row, true);
            calculateTotals();
        });

        unitSelect.addEventListener('change', function () {
            updatePrice(row);
            updateQuantityPresets(row, true);
            calculateTotals();
        });

        preset.addEventListener('change', function () {
            if (preset.value !== '') {
                quantityInput.value = preset.value;
                calculateTotals();
            }
        });

        quantityInput.addEventListener('input', function () {
            preset.value = [...preset.options].some(option =>
                option.value !== '' &&
                Number(option.value) === Number(quantityInput.value)
            ) ? quantityInput.value : '';

            calculateTotals();
        });

        row.querySelector('.remove-item').addEventListener('click', function () {
            if (container.querySelectorAll('.transaction-item').length <= 1) {
                alert('At least one product item is required.');
                return;
            }

            row.remove();
            renumberItems();
            calculateTotals();
        });

        productSelect.value = String(old?.product_id ?? selectedProductId ?? '');
        updateProductFields(row, !old);

        if (old) {
            if ([...unitSelect.options].some(option => option.value === old.unit_type)) {
                unitSelect.value = old.unit_type;
                updatePrice(row);
                updateQuantityPresets(row, false);
            }

            quantityInput.value = old.quantity ?? 1;
        }

        calculateTotals();
        return row;
    }

    function addProductToCart(productId) {
        const product = getProduct(productId);

        if (!product) {
            return;
        }

        const defaultUnit = isGeneric(product)
            ? String(product.selling_units?.[0]?.unit_name || '').trim().toLowerCase()
            : 'kg';
        const existingRow = [...container.querySelectorAll('.transaction-item')]
            .find(row =>
                row.querySelector('.product-select').value === String(productId)
                && row.querySelector('.unit-select').value === defaultUnit
            );

        if (existingRow) {
            const quantityInput = existingRow.querySelector('.quantity-input');
            quantityInput.value = (Number(quantityInput.value) + 1).toFixed(2);
            calculateTotals();
            return;
        }

        const row = addItem(null, productId);
        const quantityInput = row.querySelector('.quantity-input');
        quantityInput.value = '1';
        updateQuantityPresets(row, false);
        calculateTotals();

        if (window.matchMedia('(max-width: 1050px)').matches) {
            row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    function renumberItems() {
        container.querySelectorAll('.transaction-item').forEach((row, index) => {
            row.querySelector('h6').textContent = 'Product Item #' + (index + 1);
        });
    }

    function updateProductFields(row, resetQuantity) {
        const product = getProduct(row.querySelector('.product-select').value);
        const unitSelect = row.querySelector('.unit-select');
        const quantityInput = row.querySelector('.quantity-input');

        if (!product) {
            unitSelect.innerHTML = '';
            row.querySelector('.price-input').value = '';
            row.querySelector('.default-price-hint').textContent = '';
            row.querySelector('.stock-hint').textContent = '';
            row.querySelector('.quantity-unit-hint').textContent = '';
            row.querySelector('.quantity-equivalent').textContent = '';
            updateQuantityPresets(row, false);
            calculateTotals();
            return;
        }

        const previousUnit = unitSelect.value;
        const generic = isGeneric(product);

        const units = generic
            ? (product.selling_units || []).map(sellingUnit => ({
                value: String(sellingUnit.unit_name || '').trim().toLowerCase(),
                label: String(sellingUnit.unit_name || '').trim()
            })).filter(unit => unit.value)
            : [
                { value: 'kg', label: 'Per Kilogram (kg)' },
                { value: 'sack', label: 'Per Sack' }
            ];

        unitSelect.innerHTML = units.length
            ? units.map(unit => `
            <option value="${escapeHtml(unit.value)}">
                ${escapeHtml(unit.label)}
            </option>
        `).join('')
            : '<option value="">No active selling units configured</option>';

        if (units.some(unit => unit.value === previousUnit)) {
            unitSelect.value = previousUnit;
        } else if (units.length) {
            unitSelect.value = units[0].value;
        }

        if (generic && !units.length) {
            row.querySelector('.stock-hint').textContent =
                'This product has no active selling units. Configure one before recording a sale.';
        } else {
            row.querySelector('.stock-hint').textContent =
                `Available Stock: ${getStock(product)} ${getUnit(product)}`;
        }

        if (resetQuantity) {
            quantityInput.value = generic ? '1' : '5';
        }

        updatePrice(row);
        updateQuantityPresets(row, false);
    }

    function updatePrice(row) {
        const product = getProduct(row.querySelector('.product-select').value);
        const unit = row.querySelector('.unit-select').value;
        const price = getPrice(product, unit);

        row.querySelector('.price-input').value =
            product ? price.toFixed(2) : '';

        row.querySelector('.default-price-hint').textContent =
            product ? 'Registered Price: ' + peso(price) : '';
    }

    function updateQuantityPresets(row, resetQuantity) {
        const product = getProduct(row.querySelector('.product-select').value);
        const unit = row.querySelector('.unit-select').value;
        const preset = row.querySelector('.quantity-preset');
        const quantityInput = row.querySelector('.quantity-input');
        const hint = row.querySelector('.quantity-unit-hint');
        const equivalent = row.querySelector('.quantity-equivalent');

        if (!product) {
            preset.innerHTML = '<option value="">Select a product first</option>';
            hint.textContent = '';
            equivalent.textContent = '';
            return;
        }

        let options;

        if (isGeneric(product)) {
            options = [
                { value: '1', label: '1 ' + unit },
                { value: '2', label: '2 ' + unit },
                { value: '5', label: '5 ' + unit },
                { value: '10', label: '10 ' + unit }
            ];
            hint.textContent = 'Unit: ' + unit + '. Quantity converts to the product base unit.';
        } else if (unit === 'sack') {
            options = [
                { value: '1', label: '1 Sack' },
                { value: '2', label: '2 Sacks' },
                { value: '5', label: '5 Sacks' },
                { value: '10', label: '10 Sacks' }
            ];
            hint.textContent = 'Enter a whole number of sacks.';
        } else {
            options = [
                { value: '5', label: '5 kg' },
                { value: '10', label: '10 kg' },
                { value: '25', label: '25 kg' },
                { value: '50', label: '50 kg' }
            ];
            hint.textContent = 'Rice by kilogram: any positive quantity is allowed, including decimals.';
        }

        preset.innerHTML =
            '<option value="">Custom Quantity</option>' +
            options.map(option => `
                <option value="${option.value}">${escapeHtml(option.label)}</option>
            `).join('');

        if (resetQuantity) {
            quantityInput.value = isGeneric(product) ? '1' : (unit === 'sack' ? '1' : '5');
        }

        preset.value = options.some(option =>
            Number(option.value) === Number(quantityInput.value)
        ) ? String(quantityInput.value) : '';
    }

    function calculateTotals() {
        let total = 0;

        container.querySelectorAll('.transaction-item').forEach(row => {
            const product = getProduct(row.querySelector('.product-select').value);
            const quantity = Number(row.querySelector('.quantity-input').value || 0);
            const price = Number(row.querySelector('.price-input').value || 0);
            const unit = row.querySelector('.unit-select').value;

            const lineTotal = Math.round((quantity * price + Number.EPSILON) * 100) / 100;
            total += lineTotal;

            row.querySelector('.line-total').textContent = peso(lineTotal);

            const equivalent = row.querySelector('.quantity-equivalent');

            if (product && isGeneric(product) && quantity > 0) {
                const sellingUnit = getSellingUnit(product, unit);
                const factor = Number(sellingUnit?.conversion_factor || 0);
                const exceedsStock = !Number.isFinite(factor)
                    || factor <= 0
                    || Math.round((quantity * factor + Number.EPSILON) * 1000) / 1000
                        > getStock(product);
                const baseQuantity = Number.isFinite(factor) && factor > 0
                    ? Math.round((quantity * factor + Number.EPSILON) * 1000) / 1000
                    : 0;

                equivalent.textContent = sellingUnit
                    ? `${quantity} ${unit} = ${baseQuantity} ${getUnit(product)}`
                        + (exceedsStock ? ' — Insufficient stock' : '')
                    : 'Select a configured selling unit.';
                equivalent.className = 'quantity-equivalent d-block mt-1 fw-semibold '
                    + (exceedsStock ? 'text-danger' : 'text-primary');
                row.querySelector('.stock-hint').textContent = sellingUnit
                    ? `Available: ${factor > 0 ? (getStock(product) / factor).toFixed(2) : 'unavailable'} ${unit} (${getStock(product)} ${getUnit(product)})`
                    : `Available Stock: ${getStock(product)} ${getUnit(product)}`;
            } else if (product && unit === 'sack' && quantity > 0) {
                const kg = quantity * Number(product.sack_weight_kg || 0);
                equivalent.textContent = `${quantity} sack(s) = ${kg} kg`;
                equivalent.className = 'quantity-equivalent d-block mt-1 fw-semibold text-primary';
            } else {
                equivalent.textContent = '';
                equivalent.className = 'quantity-equivalent d-block mt-1 fw-semibold text-primary';
            }
        });

        currentGrandTotal = Math.round((total + Number.EPSILON) * 100) / 100;
        totalDisplay.textContent = peso(currentGrandTotal);
        document.getElementById('posCartCount').textContent =
            container.querySelectorAll('.transaction-item').length;
        updatePaymentSummary();
    }

    function updatePaymentSummary() {
        updateGcashReferenceField();

        const isCash = paymentMethod.value === 'cash';
        const isDebt = paymentMethod.value === 'utang';
        const hasShift = submitButton.dataset.hasShift === '1';
        cashPaymentFields.style.display = isCash ? '' : 'none';
        cashReceivedInput.required = isCash;
        customerNameInput.required = isDebt;
        customerNameRequired.textContent = isDebt ? '(Required for Utang)' : '(Optional)';
        submitButton.disabled = !hasShift && !isDebt;
        submitButton.textContent = isDebt
            ? 'Record Utang'
            : (hasShift ? 'Save Transaction' : 'Open Cash Drawer to Save');

        if (isDebt) {
            changeAmount.textContent = peso(0);
            remainingBalance.textContent = peso(currentGrandTotal);
            paymentMessage.textContent = 'The full transaction total will be recorded as an outstanding balance.';
            paymentMessage.className = 'small mt-3 text-warning fw-semibold';
            return;
        }

        if (!isCash) {
            changeAmount.textContent = peso(0);
            remainingBalance.textContent = peso(0);
            paymentMessage.textContent = 'GCash selected. No cash change is calculated.';
            paymentMessage.className = 'small mt-3 text-success';
            return;
        }

        const raw = cashReceivedInput.value.trim();
        const received = Number(raw);
        const valid = raw !== '' && Number.isFinite(received) && received >= 0;
        const difference = Math.round((received - currentGrandTotal) * 100) / 100;

        changeAmount.textContent = peso(valid && difference > 0 ? difference : 0);
        remainingBalance.textContent = peso(valid && difference < 0 ? -difference : 0);

        if (!valid) {
            paymentMessage.textContent = 'Enter cash received to calculate change.';
            paymentMessage.className = 'small mt-3 text-muted';
        } else if (difference < 0) {
            paymentMessage.textContent = 'Insufficient payment. This transaction cannot be saved yet.';
            paymentMessage.className = 'small mt-3 text-danger fw-semibold';
        } else {
            paymentMessage.textContent = 'Payment is sufficient. This transaction can be saved.';
            paymentMessage.className = 'small mt-3 text-success fw-semibold';
        }
    }

    paymentMethod.addEventListener('change', updatePaymentSummary);
    cashReceivedInput.addEventListener('input', updatePaymentSummary);

    addButton.addEventListener('click', function () {
        productSearch.focus();
    });

    form.addEventListener('submit', function (event) {
        const rows = container.querySelectorAll('.transaction-item');

        if (!rows.length) {
            event.preventDefault();
            alert('Please add at least one product item.');
            return;
        }

        for (const row of rows) {
            const product = getProduct(row.querySelector('.product-select').value);
            const unit = row.querySelector('.unit-select').value;
            const quantity = Number(row.querySelector('.quantity-input').value);
            const price = Number(row.querySelector('.price-input').value);

            if (!product || !Number.isFinite(quantity) || quantity <= 0 ||
                !Number.isFinite(price) || price < 0) {
                event.preventDefault();
                alert('Please check the product, quantity, and price for each item.');
                return;
            }

            if (isGeneric(product)) {
                const sellingUnit = getSellingUnit(product, unit);

                if (!sellingUnit) {
                    event.preventDefault();
                    alert(`Please select an active selling unit for ${product.name}.`);
                    return;
                }

                const factor = Number(sellingUnit.conversion_factor);
                const requestedBaseQuantity =
                    Math.round((quantity * factor + Number.EPSILON) * 1000) / 1000;

                if (!Number.isFinite(factor) || factor <= 0) {
                    event.preventDefault();
                    alert(`The selling-unit conversion for ${product.name} is invalid.`);
                    return;
                }

                if (requestedBaseQuantity > getStock(product)) {
                    event.preventDefault();
                    alert(`Insufficient stock for ${product.name}. Available: ${getStock(product)} ${getUnit(product)}.`);
                    return;
                }

                if (Math.abs(price - Number(sellingUnit.selling_price || 0)) > 0.009) {
                    event.preventDefault();
                    alert(`The registered price for ${product.name} has changed. Refresh the page before continuing.`);
                    return;
                }
            } else {
                if (!['kg', 'sack'].includes(unit)) {
                    event.preventDefault();
                    alert('Rice can only be sold by kilogram or sack.');
                    return;
                }

                const sackWeight = Number(product.sack_weight_kg || 0);

                if (unit === 'sack' && sackWeight < 5) {
                    event.preventDefault();
                    alert(`A valid sack weight is not configured for ${product.name}.`);
                    return;
                }

                const quantityKg = unit === 'sack' ? quantity * sackWeight : quantity;

                if (quantityKg > Number(product.stock_kg || 0)) {
                    event.preventDefault();
                    alert(`Insufficient stock for ${product.name}. Available: ${product.stock_kg} kg.`);
                    return;
                }

                const registeredPrice = getPrice(product, unit);

                if (Math.abs(price - registeredPrice) > 0.009) {
                    event.preventDefault();
                    alert(`The registered price for ${product.name} has changed. Refresh the page before continuing.`);
                    return;
                }
            }
        }

        if (paymentMethod.value === 'cash') {
            const rawCash = cashReceivedInput.value.trim();
            const cash = Number(rawCash);

            if (rawCash === '' || !Number.isFinite(cash) || cash < currentGrandTotal) {
                event.preventDefault();
                updatePaymentSummary();
                alert('Cash received must be at least the transaction total.');
                cashReceivedInput.focus();
                return;
            }
        }

        const confirmation = paymentMethod.value === 'utang'
            ? 'Record this as customer debt? The products will be deducted from inventory and the total will remain unpaid.'
            : 'Save this transaction? The sold quantities will be deducted from inventory.';

        if (!confirm(confirmation)) {
            event.preventDefault();
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Saving...';
    });

    updateGcashReferenceField();
    renderProductCatalog();
    if (oldItems.length) {
        oldItems.forEach(item => addItem(item));
    }
    calculateTotals();
});
</script>
@endif
@endsection