@extends('admin.layouts.app')

@section('title', 'Products')

@section('content')

<style>
        .products-page {
            width: 100%;
            min-width: 0;
        }

        .products-page *,
        .products-page *::before,
        .products-page *::after {
            box-sizing: border-box;
        }

        .products-page .page-heading {
            margin-bottom: 24px;
        }

        .products-page .page-heading h1 {
            margin: 0 0 8px;
            color: #203f2b;
            font-size: 28px;
            font-weight: 750;
            line-height: 1.25;
        }

        .products-page .page-heading p {
            margin: 0;
            color: #657568;
            font-size: 14px;
            line-height: 1.6;
        }

        .products-page .notice {
            margin-bottom: 20px;
            padding: 14px 16px;
            border: 1px solid #d9e6d7;
            border-radius: 9px;
            background: #f2f8f0;
            color: #285337;
        }

        .products-page .notice ul {
            margin: 8px 0 0;
            padding-left: 22px;
        }

        .products-page .products-panel {
            min-width: 0;
            padding: 24px;
            overflow: hidden;
            border: 1px solid #e1e8df;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 3px 12px rgba(29, 65, 39, 0.045);
        }

        .products-page .panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .products-page .panel-heading h2 {
            margin: 0;
            color: #244d34;
            font-size: 21px;
            font-weight: 700;
        }

        .products-page .product-count {
            display: inline-flex;
            align-items: center;
            padding: 6px 11px;
            border-radius: 20px;
            background: #edf4ec;
            color: #315a3d;
            font-size: 12px;
            font-weight: 700;
        }

        .products-page .table-wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            border: 1px solid #e0e7dd;
            border-radius: 9px;
            -webkit-overflow-scrolling: touch;
        }

        .products-page table {
            width: 100%;
            min-width: 1100px;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
        }

        .products-page thead th {
            padding: 15px 13px;
            background: #edf3eb;
            color: #294c33;
            font-size: 12px;
            font-weight: 750;
            line-height: 1.45;
            text-align: left;
            vertical-align: middle;
            white-space: normal;
            border-bottom: 1px solid #d9e3d6;
        }

        .products-page tbody td {
            padding: 11px 10px;
            color: #344238;
            font-size: 13px;
            line-height: 1.4;
            vertical-align: middle;
            border-bottom: 1px solid #e9eee7;
        }

        .products-page tbody tr:last-child td {
            border-bottom: none;
        }

        .products-page tbody tr.product-data-row {
            transition: background 0.15s ease;
        }

        .products-page tbody tr.product-data-row:hover {
            background: #f8faf7;
        }

        .products-page .product-name {
            min-width: 165px;
            color: #233e2b;
            font-size: 14px;
            font-weight: 700;
        }

        .products-page .number-value {
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .products-page .status-pill,
        .products-page .stock-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 750;
            white-space: nowrap;
        }

        .products-page .status-confirmed,
        .products-page .stock-in {
            background: #e7f5e8;
            color: #28633a;
        }

        .products-page .status-pending,
        .products-page .stock-low {
            background: #fff2d9;
            color: #875d12;
        }

        .products-page .stock-out {
            background: #fde9e7;
            color: #a33630;
        }

        .products-page .actions-cell {
            min-width: 110px;
            width: 110px;
        }

        .products-page .actions {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 7px;
            width: 100%;
            min-width: 92px;
        }

        .products-page .actions form {
            display: block;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .products-page .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 37px;
            margin: 0;
            padding: 9px 12px;
            border: 1px solid #245b38;
            border-radius: 6px;
            background: #245b38;
            color: #fff;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            line-height: 1.25;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.15s ease, border-color 0.15s ease;
        }

        .products-page .button:hover {
            border-color: #17472a;
            background: #17472a;
        }

        .products-page .button-edit {
            border-color: #d5e4d5;
            background: #edf4ec;
            color: #245536;
        }

        .products-page .button-edit:hover {
            border-color: #c3d8c3;
            background: #dfeede;
        }

        .products-page .button-remove {
            border-color: #ead5d3;
            background: #fff;
            color: #a33630;
        }

        .products-page .button-remove:hover {
            border-color: #dfb8b4;
            background: #fff2f1;
        }

        .products-page .empty-state {
            padding: 42px 20px;
            color: #657568;
            text-align: center;
        }

        .products-page .empty-state strong {
            display: block;
            margin-bottom: 7px;
            color: #304d37;
            font-size: 16px;
        }

        .products-page .edit-form-row > td {
            padding: 14px;
            background: #f7faf6;
            border-bottom: 1px solid #dce7da;
        }

        .products-page .product-edit-form {
            padding: 16px;
            border: 1px solid #dce7da;
            border-radius: 8px;
            background: #fff;
        }

        .products-page .product-edit-form h3 {
            margin: 0 0 16px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e5ece2;
            color: #244d34;
            font-size: 18px;
            font-weight: 700;
        }

        .products-page .fields {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            align-items: start;
        }

        .products-page .field {
            min-width: 0;
            margin-bottom: 10px;
        }

        .products-page .field label {
            display: block;
            margin-bottom: 5px;
            color: #374c3b;
            font-size: 12px;
            font-weight: 650;
        }

        .products-page .field input:not([type="checkbox"]),
        .products-page .field select,
        .products-page .field textarea {
            display: block;
            width: 100%;
            min-height: 38px;
            padding: 8px 10px;
            border: 1px solid #ccd8ca;
            border-radius: 6px;
            background: #fff;
            color: #263b2b;
            font: inherit;
            font-size: 13px;
        }

        .products-page .field textarea {
            min-height: 72px;
            resize: vertical;
        }

        .products-page .field input[type="file"] {
            min-height: auto;
            padding: 7px;
            background: #f9fbf8;
        }

        .products-page .field input:focus,
        .products-page .field select:focus,
        .products-page .field textarea:focus {
            outline: 2px solid #a9c9ad;
            outline-offset: 1px;
            border-color: #719a78;
        }

        .products-page .field small {
            display: block;
            margin-top: 5px;
        }

        .products-page .product-edit-form .checkbox-field {
            margin-bottom: 8px;
        }

        .products-page .product-edit-form .checkbox-field label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            cursor: pointer;
        }

        .products-page .product-edit-form .checkbox-field input[type="checkbox"] {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: #245b38;
        }

        .products-page .edit-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .products-page .edit-actions .button {
            width: auto;
            min-width: 110px;
            min-height: 36px;
        }

        .products-page .button-cancel {
            border-color: #d7dfd5;
            background: #fff;
            color: #435447;
        }

        .products-page .button-cancel:hover {
            background: #f0f3ef;
        }

        .products-page .product-qr-section {
            margin-top: 12px !important;
            padding: 14px !important;
            border: 1px solid #dce7da !important;
            border-radius: 8px !important;
            background: #fff;
        }

        .products-page .product-qr-section h4 {
            margin: 0 0 12px;
            color: #244d34;
            font-size: 16px;
        }

        .products-page .product-qr-section input {
            max-width: 100%;
        }

        .products-page .product-qr-section .button {
            width: auto;
            min-width: 130px;
        }

        @media (max-width: 1000px) {
            .products-page .fields {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .products-page .fields {
                grid-template-columns: minmax(0, 1fr);
                gap: 6px;
            }

            .products-page .edit-form-row > td {
                padding: 8px;
            }

            .products-page .product-edit-form {
                padding: 12px;
            }

            .products-page .edit-actions .button {
                flex: 1;
                min-width: 0;
            }
        }

        .products-page .muted {
            color: #657568;
            font-size: 13px;
            line-height: 1.6;
        }

        .products-page .calculator-result {
            margin: 12px 0 0;
            padding: 12px 14px;
            border: 1px solid #dbe8d8;
            border-radius: 7px;
            background: #eff6ed;
            color: #285337;
            font-size: 13px;
            line-height: 1.6;
        }

    .products-page .inventory-summary {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 16px;
                margin-bottom: 22px;
            }

            .products-page .inventory-card {
                padding: 20px;
                border: 1px solid #e1e8df;
                border-radius: 10px;
                background: #fff;
                box-shadow: 0 3px 12px rgba(29, 65, 39, 0.045);
            }

            .products-page .inventory-card span {
                display: block;
                margin-bottom: 9px;
                color: #657568;
                font-size: 13px;
            }

            .products-page .inventory-card strong {
                color: #244d34;
                font-size: 25px;
                font-weight: 750;
            }

            .products-page .inventory-card.warning strong {
                color: #946516;
            }

            .products-page .inventory-card.danger strong {
        color: #b33630;
    }

    @media (max-width: 700px) {
        .products-page .inventory-summary {
            grid-template-columns: 1fr;
            gap: 10px;
        }
    }

    .products-page .stock-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 750;
        white-space: nowrap;
    }

    .products-page .stock-in {
        background: #e7f5e8;
        color: #28633a;
    }

    .products-page .stock-low {
        background: #fff2d9;
        color: #875d12;
    }

    .products-page .stock-out {
        background: #fde9e7;
        color: #a33630;
    }

    .products-page .product-filters {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 18px;
    }

    .products-page .product-filters input,
    .products-page .product-filters select {
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid #ccd8ca;
        border-radius: 7px;
        background: #fff;
        color: #263b2b;
        font: inherit;
        font-size: 13px;
    }

    .products-page .product-filters input {
        flex: 1;
        min-width: 220px;
    }

    .products-page .product-filters select {
        min-width: 160px;
    }

    .products-page #filterResultCount {
        color: #657568;
        font-size: 12px;
    }

    /* Product table: readable text and horizontal scrolling */
    .products-page .table-wrap.products-table-wrapper {
        display: block;
        width: 100%;
        max-width: 100%;
        container-name: products-table;
        container-type: inline-size;
        overflow-x: auto;
        overflow-y: visible;
        border: 1px solid #e0e7dd;
        border-radius: 9px;
        -webkit-overflow-scrolling: touch;
    }

    .products-page table.products-table {
        width: 100%;
        min-width: 1150px;
        table-layout: auto;
        border-collapse: separate;
        border-spacing: 0;
        background: #fff;
        font-size: 11px;
    }

    .products-page .products-table th {
        padding: 9px 8px;
        background: #edf3eb;
        color: #294c33;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.35;
        text-align: left;
        vertical-align: middle;
        white-space: normal;
    }

    .products-page .products-table td {
        padding: 8px;
        color: #344238;
        font-size: 11px;
        line-height: 1.35;
        vertical-align: middle;
        overflow-wrap: normal;
        word-break: normal;
    }

    .products-page .products-table .product-name {
        min-width: 165px;
        width: 165px;
        color: #233e2b;
        font-size: 12px;
        font-weight: 700;
    }

    .products-page .products-table .number-value {
        min-width: 95px;
        text-align: right;
        white-space: nowrap;
        font-size: 11px;
        font-variant-numeric: tabular-nums;
    }

    .products-page .product-identity {
        display: flex;
        align-items: center;
        gap: 7px;
        min-width: 150px;
    }

    .products-page .product-image,
    .products-page .product-image-placeholder {
        width: 40px;
        height: 40px;
        flex: 0 0 40px;
        object-fit: cover;
        border: 1px solid #e0e7dd;
        border-radius: 7px;
        background: #f5f7f4;
    }

    .products-page .product-image-placeholder {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #657568;
        font-size: 11px;
    }

    .products-page .products-table .muted {
        color: #657568;
        font-size: 10px;
    }

    .products-page .products-table .status-pill,
    .products-page .products-table .stock-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 4px 7px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
    }

    .products-page .products-table .actions-cell {
        width: 100px;
        min-width: 100px;
        text-align: center;
    }

    .products-page .products-table .actions {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
        min-width: 90px;
    }

    .products-page .products-table .actions form {
        width: 100%;
        margin: 0;
    }

    .products-page .products-table .actions .button {
        width: 100%;
        min-height: 31px;
        padding: 6px 8px;
        font-size: 10px;
        white-space: nowrap;
    }

    .products-page .products-table tbody tr.product-data-row:hover {
        background: #f8faf7;
    }

    .products-page .products-table tbody tr.product-data-row td {
        border-bottom: 1px solid #e9eee7;
    }

    /* Keep the edit form comfortable to use */
    .products-page .edit-form-row > td {
        padding: 14px;
        background: #f7faf6;
    }

    .products-page .product-edit-form {
        width: 100%;
        min-width: 0;
    }

    .products-page .product-edit-form .fields {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
    }

    .products-page .product-edit-form .field {
        min-width: 0;
    }

    .products-page .product-edit-form input:not([type="checkbox"]),
    .products-page .product-edit-form select,
    .products-page .product-edit-form textarea {
        max-width: 100%;
    }

    .products-page .edit-content-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 250px;
        gap: 18px;
        align-items: start;
    }

    .products-page .product-qr-section {
        min-width: 0;
        margin-top: 0 !important;
        padding: 14px !important;
        border: 1px solid #dce7da !important;
        border-radius: 8px !important;
        background: #fff;
    }

    .products-page .product-qr-section .qr-image-container {
        display: flex;
        justify-content: center;
        margin: 12px 0;
    }

    .products-page .product-qr-section .qr-image-container img,
    .products-page .product-qr-section .qr-image-container canvas {
        display: block;
        width: 150px !important;
        height: 150px !important;
        max-width: 100%;
    }

    .products-page .fields .product-qr-section {
        grid-column: 2 / 4;
        grid-row: auto;
        align-self: start;
        margin: 0 !important;
        padding: 12px !important;
        min-width: 0;
    }

    .products-page .product-qr-section .qr-image-container {
        margin: 10px 0;
    }

    .products-page .product-qr-section .qr-image-container img,
    .products-page .product-qr-section .qr-image-container canvas {
        width: 120px !important;
        height: 120px !important;
    }

    @media (max-width: 1000px) {
        .products-page .fields .product-qr-section {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 900px) {
        .products-page .products-panel {
            padding: 16px;
        }

        .products-page .product-edit-form .fields {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .products-page .edit-content-layout {
            grid-template-columns: minmax(0, 1fr);
        }
    }

    @media (max-width: 600px) {
        .products-page .products-panel {
            padding: 12px;
        }

        .products-page .product-filters {
            align-items: stretch;
            flex-direction: column;
        }
    }

    .products-page .page-heading {
        padding: 22px 24px;
        margin-bottom: 18px;
        border: 1px solid #e0e8e1;
        border-radius: 14px;
        background: linear-gradient(110deg, #fff 0%, #f2f8f2 100%);
    }

    .products-page .inventory-summary {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .products-page .inventory-card {
        position: relative;
        overflow: hidden;
        min-height: 100px;
        padding: 17px 18px 17px 22px;
        border-color: #e0e8e1;
        border-radius: 12px;
        box-shadow: 0 4px 16px rgba(27, 55, 34, .045);
    }

    .products-page .inventory-card::before {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 4px;
        background: #4a8756;
        content: "";
    }

    .products-page .inventory-card.warning::before {
        background: #d99a2b;
    }

    .products-page .inventory-card.danger::before {
        background: #c94b41;
    }

    .products-page .inventory-card span {
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 700;
    }

    .products-page .inventory-card strong {
        font-size: 26px;
    }

    .products-page .products-panel {
        padding: 20px;
        border-color: #e0e8e1;
        border-radius: 13px;
        box-shadow: 0 5px 18px rgba(27, 55, 34, .045);
    }

    .products-page .panel-heading h2 {
        font-size: 20px;
    }

    .products-page .panel-heading > a {
        display: inline-flex !important;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        padding: 10px 16px !important;
        border: 1px solid #245b38 !important;
        border-radius: 8px !important;
        background: #245b38 !important;
        font-size: 13px;
        transition: background .15s ease;
    }

    .products-page .panel-heading > a:hover {
        background: #17472a !important;
    }

    .products-page .product-count {
        padding: 7px 12px;
        font-size: 12px;
    }

    .products-page .product-filters {
        gap: 10px;
        padding: 13px;
        border: 1px solid #e5ece4;
        border-radius: 10px;
        background: #f8faf8;
    }

    .products-page .product-filters input,
    .products-page .product-filters select {
        min-height: 42px;
        border-color: #d3ded4;
        border-radius: 8px;
        font-size: 13px;
    }

    .products-page #filterResultCount {
        flex: 0 0 auto;
        padding: 0 4px;
        font-weight: 600;
    }

    .products-page .table-wrap.products-table-wrapper {
        border-color: #e2e9e1;
        border-radius: 10px;
    }

    .products-page table.products-table {
        min-width: 1120px;
        font-size: 13px;
    }

    .products-page .products-table th {
        padding: 13px 12px;
        border-bottom: 1px solid #d9e3d6;
        font-size: 12px;
        line-height: 1.45;
    }

    .products-page .products-table td {
        padding: 12px;
        font-size: 13px;
        line-height: 1.45;
    }

    .products-page .products-table .product-name {
        min-width: 200px;
        width: 200px;
        font-size: 14px;
    }

    .products-page .products-table .number-value {
        min-width: 110px;
        font-size: 13px;
    }

    .products-page .product-identity {
        min-width: 180px;
        gap: 10px;
    }

    .products-page .product-image,
    .products-page .product-image-placeholder {
        width: 48px;
        height: 48px;
        flex-basis: 48px;
    }

    .products-page .products-table .muted {
        font-size: 11px;
    }

    .products-page .products-table .status-pill,
    .products-page .products-table .stock-status {
        padding: 6px 9px;
        font-size: 11px;
    }

    .products-page .products-table .actions-cell {
        width: 115px;
        min-width: 115px;
    }

    .products-page .products-table .actions .button {
        min-height: 36px;
        padding: 8px 10px;
        font-size: 12px;
    }

    .products-page .products-table th.actions-cell,
    .products-page .products-table td.actions-cell {
        position: sticky;
        z-index: 1;
        right: 0;
        background: #fff;
        box-shadow: -7px 0 10px rgba(27, 55, 34, .07);
    }

    .products-page .products-table th.actions-cell {
        z-index: 2;
        background: #edf3eb;
    }

    @container products-table (max-width: 1400px) {
        .products-page table.products-table {
            min-width: 0;
            table-layout: fixed;
        }

        .products-page .products-table th:nth-child(4),
        .products-page .products-table td:nth-child(4),
        .products-page .products-table th:nth-child(5),
        .products-page .products-table td:nth-child(5),
        .products-page .products-table th:nth-child(6),
        .products-page .products-table td:nth-child(6),
        .products-page .products-table th:nth-child(7),
        .products-page .products-table td:nth-child(7),
        .products-page .products-table th:nth-child(9),
        .products-page .products-table td:nth-child(9),
        .products-page .products-table th:nth-child(11),
        .products-page .products-table td:nth-child(11) {
            display: none;
        }

        .products-page .products-table th.select-product-column,
        .products-page .products-table td.select-product-column {
            width: 34px;
            min-width: 34px;
            padding-right: 5px;
            padding-left: 5px;
        }

        .products-page .products-table th:nth-child(2),
        .products-page .products-table td:nth-child(2) {
            width: 34%;
        }

        .products-page .products-table th:nth-child(3),
        .products-page .products-table td:nth-child(3) {
            width: 20%;
        }

        .products-page .products-table th:nth-child(8),
        .products-page .products-table td:nth-child(8) {
            width: 20%;
        }

        .products-page .products-table th:nth-child(10),
        .products-page .products-table td:nth-child(10) {
            width: 15%;
        }

        .products-page .products-table .product-name,
        .products-page .products-table .number-value {
            min-width: 0;
        }

        .products-page .product-identity {
            min-width: 0;
            gap: 6px;
        }

        .products-page .product-image,
        .products-page .product-image-placeholder {
            width: 34px;
            height: 34px;
            flex-basis: 34px;
        }

        .products-page .products-table .actions-cell {
            width: 86px;
            min-width: 86px;
            padding-right: 7px;
            padding-left: 7px;
        }

        .products-page .products-table .actions {
            min-width: 0;
            gap: 5px;
        }

        .products-page .products-table .actions .button {
            min-height: 30px;
            padding: 6px 5px;
            font-size: 10px;
        }
    }

    @media (max-width: 900px) {
        .products-page .inventory-summary {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
        }

        .products-page .inventory-card {
            padding: 15px 14px 15px 18px;
        }
    }

    @media (max-width: 600px) {
        .products-page .page-heading {
            padding: 17px;
        }

        .products-page .page-heading h1 {
            font-size: 24px;
        }

        .products-page .inventory-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .products-page .inventory-card {
            min-height: 88px;
            padding: 13px 11px 13px 16px;
        }

        .products-page .inventory-card strong {
            font-size: 22px;
        }

        .products-page .products-panel {
            padding: 14px;
        }

        .products-page .panel-heading {
            align-items: stretch;
        }

        .products-page .panel-heading > * {
            flex: 1 1 100%;
        }

        .products-page .product-count {
            justify-content: center;
        }
    }

    .products-page .bulk-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 9px;
            margin-bottom: 12px;
            padding: 12px;
            border: 1px solid #e1e9e1;
            border-radius: 9px;
            background: #f7faf7;
        }

        .products-page .bulk-actions[hidden] {
            display: none;
        }

        .products-page .bulk-actions select,
        .products-page .bulk-actions button {
            width: auto;
            min-height: 38px;
            padding: 8px 12px;
            border-radius: 7px;
        }

        .products-page .bulk-actions form {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 9px;
            margin: 0;
        }

        .products-page .bulk-actions select {
            border: 1px solid #ccd8ca;
            background: #fff;
        }

        .products-page .select-product-column {
            width: 42px;
            min-width: 42px;
            text-align: center !important;
        }

        .products-page .select-product-checkbox {
            width: 17px;
            height: 17px;
            margin: 0;
            accent-color: #245b38;
            vertical-align: middle;
        }

        .products-page .product-edit-modal {
            width: min(980px, calc(100vw - 32px));
            max-width: none;
            max-height: min(84vh, 820px);
            padding: 0;
            overflow: auto;
            border: 1px solid #dce7da;
            border-radius: 14px;
            background: #f8faf8;
            box-shadow: 0 24px 80px rgba(11, 35, 18, .28);
        }

        .products-page .product-edit-modal::backdrop {
            background: rgba(15, 31, 19, .58);
            backdrop-filter: blur(2px);
        }

        .products-page .product-edit-modal-header {
            position: sticky;
            z-index: 2;
            top: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 15px 20px;
            border-bottom: 1px solid #dce7da;
            background: #fff;
        }

        .products-page .product-edit-modal-header h2 {
            margin: 0;
            color: #244d34;
            font-size: 19px;
        }

        .products-page .product-edit-modal-close {
            width: 38px;
            min-height: 38px;
            border: 1px solid #dbe4da;
            border-radius: 8px;
            background: #fff;
            color: #34483a;
            cursor: pointer;
            font-size: 20px;
        }

        .products-page .product-edit-modal-body {
            padding: 16px;
        }

        .products-page .product-edit-content {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 260px;
            gap: 14px;
            align-items: start;
        }

        .products-page .product-edit-form {
            min-width: 0;
            padding: 18px;
            border-color: #e0e8e1;
            border-radius: 11px;
        }

        .products-page .product-edit-form h3 {
            margin-bottom: 15px;
            font-size: 17px;
        }

        .products-page .product-edit-form .fields {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .products-page .edit-image-preview {
            display: block;
            width: 96px;
            height: 96px;
            margin-bottom: 10px;
            border: 1px solid #e0e7dd;
            border-radius: 9px;
            background: #f5f7f4;
            object-fit: cover;
        }

        .products-page .selling-units-editor {
            grid-column: 1 / -1;
            padding: 14px;
            border: 1px solid #dce7da;
            border-radius: 9px;
            background: #f8faf8;
        }

        .products-page .selling-units-editor h4 {
            margin: 0 0 5px;
            color: #244d34;
            font-size: 14px;
        }

        .products-page .selling-unit-row {
            display: grid;
            grid-template-columns: minmax(100px, 1.1fr) repeat(3, minmax(90px, 1fr)) auto auto;
            gap: 8px;
            align-items: end;
            padding: 10px 0;
            border-bottom: 1px solid #e4ebe3;
        }

        .products-page .selling-unit-row:last-child {
            border-bottom: 0;
        }

        .products-page .selling-unit-row .field {
            margin: 0;
        }

        .products-page .selling-unit-row .field label {
            font-size: 11px;
        }

        .products-page .selling-unit-row .field input {
            min-height: 36px;
            padding: 7px;
        }

        .products-page .selling-unit-active {
            display: flex;
            min-height: 36px;
            align-items: center;
            gap: 6px;
            color: #354c3b;
            font-size: 12px;
            white-space: nowrap;
        }

        .products-page .selling-unit-active input {
            width: 16px;
            height: 16px;
            accent-color: #245b38;
        }

        .products-page .selling-unit-remove,
        .products-page .add-selling-unit {
            width: auto;
            min-height: 36px;
            padding: 7px 10px;
            border-radius: 7px;
            font-size: 11px;
        }

        .products-page .selling-unit-remove {
            border-color: #ead5d3;
            background: #fff;
            color: #a33630;
        }

        .products-page .quick-stock-adjustment {
            padding: 16px;
            border: 1px solid #dce7da;
            border-radius: 11px;
            background: #fff;
        }

        .products-page .quick-stock-adjustment h3,
        .products-page .product-qr-section h4 {
            margin: 0 0 12px;
            color: #244d34;
            font-size: 15px;
        }

        .products-page .quick-stock-current {
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 8px;
            background: #f1f7f1;
            color: #315a3d;
            font-size: 13px;
            font-weight: 700;
        }

        .products-page .quick-stock-adjustment .field {
            margin-bottom: 10px;
        }

        .products-page .quick-stock-adjustment .button {
            min-height: 40px;
        }

        .products-page .product-qr-section {
            grid-column: 2;
        }

        @media (max-width: 780px) {
            .products-page .product-edit-content {
                grid-template-columns: minmax(0, 1fr);
            }

            .products-page .product-qr-section {
                grid-column: auto;
            }

            .products-page .selling-unit-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 520px) {
            .products-page .product-edit-modal {
                width: calc(100vw - 16px);
                max-height: 94vh;
            }

            .products-page .product-edit-modal-body {
                padding: 8px;
            }

            .products-page .product-edit-form .fields {
                grid-template-columns: minmax(0, 1fr);
            }

            .products-page .selling-units-editor {
                grid-column: auto;
                padding: 10px;
            }

            .products-page .selling-unit-row {
                grid-template-columns: minmax(0, 1fr);
                align-items: stretch;
            }
        }

    .products-page {
        --products-blue: #1672e8;
        --products-navy: #18395f;
        --products-border: #d7e4f1;
        color: #233b59;
    }

    .products-page .page-heading {
        padding: 18px 20px;
        border: 1px solid var(--products-border);
        border-radius: 8px;
        background: #fff;
    }

    .products-page .page-heading h1,
    .products-page .panel-heading h2 {
        color: var(--products-navy);
    }

    .products-page .page-heading h1 {
        margin-bottom: 4px;
        font-size: 22px;
    }

    .products-page .page-heading p {
        font-size: 12px;
    }

    .products-page .inventory-summary {
        gap: 10px;
        margin-bottom: 14px;
    }

    .products-page .inventory-card {
        min-height: 78px;
        padding: 13px 15px 13px 19px;
        border-color: var(--products-border);
        border-radius: 8px;
        box-shadow: none;
    }

    .products-page .inventory-card::before {
        background: var(--products-blue);
    }

    .products-page .inventory-card.warning::before {
        background: #e6a323;
    }

    .products-page .inventory-card.danger::before {
        background: #d9534f;
    }

    .products-page .inventory-card span {
        margin-bottom: 4px;
        color: #58708d;
        font-size: 11px;
    }

    .products-page .inventory-card strong {
        color: var(--products-navy);
        font-size: 21px;
    }

    .products-page .products-panel {
        padding: 14px;
        border-color: var(--products-border);
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(24, 57, 95, .04);
    }

    .products-page .panel-heading {
        margin-bottom: 12px;
    }

    .products-page .panel-heading h2 {
        font-size: 17px;
    }

    .products-page .panel-heading > a,
    .products-page .panel-heading > a:hover {
        border-color: var(--products-blue) !important;
        background: var(--products-blue) !important;
        border-radius: 5px !important;
        padding: 8px 13px !important;
        font-size: 12px;
    }

    .products-page .panel-heading > a:hover {
        border-color: #0e5fc8 !important;
        background: #0e5fc8 !important;
    }

    .products-page .product-count {
        border-radius: 5px;
        background: #edf5ff;
        color: #245b9c;
        font-size: 11px;
    }

    .products-page .product-filters {
        gap: 8px;
        padding: 10px;
        border-color: var(--products-border);
        border-radius: 6px;
        background: #f7faff;
    }

    .products-page .product-filters input,
    .products-page .product-filters select {
        min-height: 36px;
        border-color: #d1dfed;
        border-radius: 5px;
        color: #344f70;
        font-size: 12px;
    }

    .products-page .table-wrap.products-table-wrapper {
        border-color: var(--products-border);
        border-radius: 6px;
    }

    .products-page .products-table th {
        border-bottom: 1px solid #d5e2ef;
        background: #f1f6fc;
        color: #304d70;
        font-size: 9px;
        letter-spacing: .025em;
        text-transform: uppercase;
    }

    .products-page .products-table td {
        border-bottom-color: #e7eef6;
        color: #354c67;
        font-size: 10px;
    }

    .products-page .products-table tbody tr.product-data-row:hover {
        background: #f6faff;
    }

    .products-page .products-table .product-name {
        color: #203d60;
        font-size: 11px;
    }

    .products-page .product-image,
    .products-page .product-image-placeholder {
        border-color: #d9e5f0;
        border-radius: 4px;
    }

    .products-page .products-table th.actions-cell,
    .products-page .products-table td.actions-cell {
        background: #fff;
        box-shadow: -5px 0 8px rgba(29, 66, 105, .06);
    }

    .products-page .products-table th.actions-cell {
        background: #f1f6fc;
    }

    .products-page .products-table .button-edit {
        border-color: #8db9f1;
        background: #fff;
        color: #1768ce;
    }

    .products-page .products-table .button-edit:hover {
        border-color: #4a93eb;
        background: #edf5ff;
    }

    .products-page .products-table .button-remove {
        border-color: #f0d7d5;
        background: #fff;
        color: #bb4945;
    }

    .products-page .products-table .button-remove:hover {
        border-color: #e3b2ae;
        background: #fff6f5;
    }

    .products-page .status-confirmed,
    .products-page .stock-in {
        background: #dcf8eb;
        color: #087c4b;
    }

    .products-page .status-pending,
    .products-page .stock-low {
        background: #fff3d8;
        color: #96630a;
    }

    .products-page .stock-out {
        background: #fde7e6;
        color: #b33530;
    }

    .products-page button,
    .products-page .button,
    .products-page .panel-heading > a {
        border-color: #245b38 !important;
        background: #245b38 !important;
        color: #fff !important;
    }

    .products-page button:hover,
    .products-page .button:hover,
    .products-page .panel-heading > a:hover {
        border-color: #17472a !important;
        background: #17472a !important;
        color: #fff !important;
    }

    @media (max-width: 700px) {
        .products-page .page-heading {
            padding: 14px;
        }

        .products-page .inventory-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .products-page .inventory-card:first-child {
            grid-column: 1 / -1;
        }

        .products-page .products-panel {
            padding: 10px;
        }
    }

    :root[data-theme="dark"] .products-page {
        --products-border: #293149;
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .products-page .page-heading,
    :root[data-theme="dark"] .products-page .products-panel,
    :root[data-theme="dark"] .products-page .inventory-card,
    :root[data-theme="dark"] .products-page .product-edit-form,
    :root[data-theme="dark"] .products-page .product-qr-section {
        border-color: var(--products-border);
        background: #131b2e;
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .products-page .page-heading h1,
    :root[data-theme="dark"] .products-page .panel-heading h2,
    :root[data-theme="dark"] .products-page .inventory-card strong,
    :root[data-theme="dark"] .products-page .product-edit-form h3,
    :root[data-theme="dark"] .products-page .product-qr-section h4,
    :root[data-theme="dark"] .products-page .products-table .product-name {
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .products-page .page-heading p,
    :root[data-theme="dark"] .products-page .inventory-card span,
    :root[data-theme="dark"] .products-page .muted,
    :root[data-theme="dark"] .products-page #filterResultCount,
    :root[data-theme="dark"] .products-page .products-table .muted {
        color: #aab6cb;
    }

    :root[data-theme="dark"] .products-page .product-count {
        background: #1a2b43;
        color: #b8d7ff;
    }

    :root[data-theme="dark"] .products-page .product-filters,
    :root[data-theme="dark"] .products-page .bulk-actions,
    :root[data-theme="dark"] .products-page .edit-form-row > td,
    :root[data-theme="dark"] .products-page .edit-form-row {
        border-color: var(--products-border);
        background: #11192a;
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .products-page .product-filters input,
    :root[data-theme="dark"] .products-page .product-filters select,
    :root[data-theme="dark"] .products-page .field input:not([type="checkbox"]),
    :root[data-theme="dark"] .products-page .field select,
    :root[data-theme="dark"] .products-page .field textarea,
    :root[data-theme="dark"] .products-page .bulk-actions select {
        border-color: #3a465e;
        background: #11192a;
        color: #ecf1ff;
    }

    :root[data-theme="dark"] .products-page .field label {
        color: #d3dbed;
    }

    :root[data-theme="dark"] .products-page .field input::placeholder,
    :root[data-theme="dark"] .products-page .field textarea::placeholder,
    :root[data-theme="dark"] .products-page .product-filters input::placeholder {
        color: #9aa5bd;
    }

    :root[data-theme="dark"] .products-page .table-wrap.products-table-wrapper,
    :root[data-theme="dark"] .products-page table.products-table {
        border-color: var(--products-border);
        background: #11192a;
    }

    :root[data-theme="dark"] .products-page .products-table th,
    :root[data-theme="dark"] .products-page .products-table th.actions-cell {
        border-bottom-color: var(--products-border);
        background: #1a2439;
        color: #d3dbed;
    }

    :root[data-theme="dark"] .products-page .products-table td,
    :root[data-theme="dark"] .products-page .products-table td.actions-cell {
        border-bottom-color: var(--products-border);
        background: #11192a;
        color: #d3dbed;
        box-shadow: -5px 0 8px rgba(0, 0, 0, .14);
    }

    :root[data-theme="dark"] .products-page .products-table tbody tr.product-data-row:hover td {
        background: #1a2439;
    }

    :root[data-theme="dark"] .products-page .products-table .product-image,
    :root[data-theme="dark"] .products-page .products-table .product-image-placeholder {
        border-color: var(--products-border);
        background: #1a2439;
        color: #aab6cb;
    }

    :root[data-theme="dark"] .products-page .product-edit-form h3,
    :root[data-theme="dark"] .products-page .product-qr-section,
    :root[data-theme="dark"] .products-page .selling-unit-row,
    :root[data-theme="dark"] .products-page .edit-actions {
        border-color: var(--products-border);
    }

    :root[data-theme="dark"] .products-page .button-edit,
    :root[data-theme="dark"] .products-page .button-cancel {
        border-color: #3a465e;
        background: #1a2439;
        color: #d3dbed;
    }

    :root[data-theme="dark"] .products-page .button-remove {
        border-color: #653b42;
        background: #332027;
        color: #ffb4ad;
    }

    :root[data-theme="dark"] .products-page .calculator-result {
        border-color: #35533e;
        background: #172a22;
        color: #c9ead0;
    }

    :root[data-theme="dark"] .products-page .notice {
        border-color: #35533e;
        background: #172a22;
        color: #c9ead0;
    }

</style>

<div class="products-page">

    <div class="page-heading">
        <h1>Product Management</h1>
        <p>
            Manage selling prices, product costs, sack weights, stock balances, and price confirmation.
        </p>
    </div>

    @php
        $allProducts = collect($products ?? []);
        $totalStockUnits = $allProducts->sum(function ($product) {
            return ($product->inventory_type ?? 'rice') === 'generic'
                ? (float) ($product->stock_base_quantity ?? 0)
                : (float) ($product->stock_kg ?? 0);
        });

        $lowStockCount = $allProducts->filter(function ($product) {
            if (!($product->is_active ?? false)) {
                return false;
            }

            if (($product->inventory_type ?? 'rice') === 'generic') {
                $stock = (float) ($product->stock_base_quantity ?? 0);
                $threshold = (float) ($product->low_stock_threshold_quantity ?? 0);
            } else {
                $stock = (float) ($product->stock_kg ?? 0);
                $threshold = (float) ($product->low_stock_threshold ?? 0);
            }

            return $stock > 0 && $stock <= $threshold;
        })->count();

        $outOfStockCount = $allProducts->filter(function ($product) {
            if (!($product->is_active ?? false)) {
                return false;
            }

            return ($product->inventory_type ?? 'rice') === 'generic'
                ? (float) ($product->stock_base_quantity ?? 0) <= 0
                : (float) ($product->stock_kg ?? 0) <= 0;
        })->count();
    @endphp

    <div class="inventory-summary">
        <div class="inventory-card">
            <span>Total Products</span>
            <strong>{{ $allProducts->count() }}</strong>
        </div>

        <div class="inventory-card warning">
            <span>Low Stock Products</span>
            <strong>{{ number_format($lowStockCount) }}</strong>
        </div>

        <div class="inventory-card danger">
            <span>Out of Stock</span>
            <strong>{{ number_format($outOfStockCount) }}</strong>
        </div>
    </div>

    @if (session('success'))
        <div class="notice" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="notice" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="notice" role="alert">
            <strong>Please correct the following errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="products-panel">
        <div class="panel-heading">
            <h2>Products</h2>

            <a href="{{ route('admin.products.create') }}"
               class="add-product-button">
                + Add Product
            </a>

            @if (isset($products))
                <span class="product-count">
                    {{ $products->count() }}
                    {{ $products->count() === 1 ? 'Product' : 'Products' }}
                </span>
            @endif
        </div>

        @if (isset($products) && $products->isNotEmpty())

            <div class="product-filters">
                <input
                    type="search"
                    id="productSearch"
                    placeholder="Search product name..."
                    aria-label="Search product name"
                    oninput="filterProducts()"
                >

                <select
                    id="stockFilter"
                    aria-label="Filter by stock status"
                    onchange="filterProducts()"
                >
                    <option value="all">All Products</option>
                    <option value="in">In Stock</option>
                    <option value="low">Low Stock</option>
                    <option value="out">Out of Stock</option>
                </select>

                <select id="categoryFilter" aria-label="Filter by product category or type" onchange="filterProducts()">
                    <option value="all">All Categories &amp; Types</option>
                    <option value="rice">Rice</option>
                    <option value="generic">General Inventory</option>
                    @foreach ($allProducts->pluck('category')->filter()->unique()->sort() as $category)
                        <option value="category:{{ strtolower($category) }}">{{ $category }}</option>
                    @endforeach
                </select>

                <span id="filterResultCount" aria-live="polite">
                    {{ $products->count() }} products
                </span>
            </div>

            <div class="bulk-actions" id="bulkActions" hidden>
                <form method="POST" action="{{ route('admin.products.bulk') }}" id="bulkProductsForm">
                    @csrf
                    <label class="muted" for="bulkProductAction">Bulk action for <strong id="selectedProductCount">0</strong> selected</label>
                    <select id="bulkProductAction" name="action" required>
                        <option value="activate">Activate</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="remove">Remove selected products</option>
                    </select>
                    <button class="button" type="submit">Apply</button>
                </form>
            </div>

            <div class="table-wrap products-table-wrapper">
                <table class="products-table">
                    <thead>
                        <tr>
                            <th class="select-product-column">
                                <input id="selectAllProducts" class="select-product-checkbox" type="checkbox" aria-label="Select all visible products">
                            </th>
                            <th>Product</th>
                            <th>Price per Kg / Unit</th>
                            <th>Price per Sack</th>
                            <th>Sack Weight (Kg)</th>
                            <th>Cost per Sack</th>
                            <th>Target Profit (%)</th>
                            <th>Available Stock</th>
                            <th>Low Stock Alert</th>
                            <th>Stock Status</th>
                            <th>Price Confirmation</th>
                            <th class="actions-cell">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($products as $product)
                            @php
                                $isGenericProduct = ($product->inventory_type ?? 'rice') === 'generic';
                                $productStock = $isGenericProduct
                                    ? (float) ($product->stock_base_quantity ?? 0)
                                    : (float) ($product->stock_kg ?? 0);
                                $productThreshold = $isGenericProduct
                                    ? (float) ($product->low_stock_threshold_quantity ?? 0)
                                    : (float) ($product->low_stock_threshold ?? 0);
                                $productUnit = $isGenericProduct
                                    ? ($product->base_unit ?: $product->stock_unit ?: 'unit')
                                    : 'kg';
                                $baseSellingUnit = $isGenericProduct
                                    ? $product->sellingUnits->firstWhere(
                                        'unit_name',
                                        strtolower($productUnit)
                                    )
                                    : null;
                                $productUnitPrice = $isGenericProduct
                                    ? ($baseSellingUnit?->selling_price ?? $product->price_per_unit)
                                    : $product->price_per_kg;
                                $useOldProductInput = (string) old('_product_id') === (string) $product->id;
                            @endphp
                            <tr
                                class="product-data-row"
                                data-product-id="{{ $product->id }}"
                                data-product-name="{{ strtolower($product->name) }}"
                                data-inventory-type="{{ $isGenericProduct ? 'generic' : 'rice' }}"
                                data-category="{{ strtolower(trim($product->category ?: 'uncategorized')) }}"
                                data-selling-units="{{ $product->sellingUnits->values()->toJson() }}"
                                data-stock-status="{{ $productStock <= 0
                                    ? 'out'
                                    : ($productStock <= $productThreshold
                                        ? 'low'
                                        : 'in') }}"
                            >
                                <td class="select-product-column">
                                    <input
                                        class="select-product-checkbox product-select-checkbox"
                                        type="checkbox"
                                        name="product_ids[]"
                                        value="{{ $product->id }}"
                                        form="bulkProductsForm"
                                        aria-label="Select {{ $product->name }}"
                                    >
                                </td>
                                <td class="product-name">
                                    <div class="product-identity">
                                        <img
                                            src="{{ $product->image
                                                ? asset('storage/' . $product->image)
                                                : asset('images/default-product.svg') }}"
                                            alt="{{ $product->name }}"
                                            class="product-image"
                                            loading="eager"
                                            onerror="this.onerror=null; this.src='{{ asset('images/default-product.svg') }}';"
                                        >

                                        <span>{{ $product->name }}</span>
                                    </div>
                                </td>

                                <td class="number-value">
                                    {{ number_format((float) $productUnitPrice, 2) }}
                                    @if ($isGenericProduct)
                                        <br><span class="muted">per {{ $productUnit }}</span>
                                    @endif
                                </td>

                                <td class="number-value">
                                    {{ $isGenericProduct
                                        ? '—'
                                        : number_format((float) $product->price_per_sack, 2) }}
                                </td>

                                <td class="number-value">
                                    {{ $isGenericProduct
                                        ? '—'
                                        : number_format((float) $product->sack_weight_kg, 2) }}
                                </td>

                                <td class="number-value">
                                    @if (!$isGenericProduct && $product->cost_per_sack !== null)
                                        {{ number_format((float) $product->cost_per_sack, 2) }}
                                    @else
                                        <span class="muted">Not set</span>
                                    @endif
                                </td>

                                <td class="number-value">
                                    {{ number_format((float) ($product->profit_percentage ?? 0), 2) }}%
                                </td>

                                <td class="number-value">
                                    @if ($isGenericProduct)
                                        <strong>{{ number_format($productStock, 2) }} {{ $productUnit }}</strong>
                                    @else
                                        @php
                                            $weight = (float) $product->sack_weight_kg;
                                            $sacks = $weight > 0 ? (int) floor($productStock / $weight) : 0;
                                            $remaining = $weight > 0 ? $productStock - ($sacks * $weight) : $productStock;
                                        @endphp

                                        <strong>{{ $sacks }} sacks</strong>
                                    @endif
                                </td>

                                <td class="number-value">
                                    @if ($isGenericProduct)
                                        {{ number_format($productThreshold, 2) }} {{ $productUnit }}
                                    @else
                                        {{ $product->sack_weight_kg > 0
                                        ? number_format(
                                            (float) $product->low_stock_threshold
                                                / (float) $product->sack_weight_kg,
                                            2
                                        )
                                        : '—' }}
                                    @endif
                                </td>

                                <td>
                                    @if ($productStock <= 0)
                                        <span class="stock-status stock-out">Out of Stock</span>
                                    @elseif ($productStock <= $productThreshold)
                                        <span class="stock-status stock-low">Low Stock</span>
                                    @else
                                        <span class="stock-status stock-in">In Stock</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($product->price_confirmed)
                                        <span class="status-pill status-confirmed">
                                            Confirmed
                                        </span>
                                    @else
                                        <span class="status-pill status-pending">
                                            Pending
                                        </span>
                                    @endif
                                </td>

                                <td class="actions-cell">
                                    <div class="actions">
                                        <a
                                            class="button button-edit"
                                            href="{{ route('admin.products.edit', $product) }}"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.products.destroy', $product) }}"
                                            onsubmit="return confirm('Are you sure you want to remove this product? Products with sales history may be hidden instead of permanently deleted.');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="button button-remove">
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                            <tr
                                id="product-form-row-{{ $product->id }}"
                                class="edit-form-row"
                                style="display: none;"
                            >
                                <td colspan="12">
                                    <div class="product-edit-content" id="product-edit-content-{{ $product->id }}">
                                    <form
                                        method="POST"
                                        action="{{ route('admin.products.update', $product) }}"
                                        class="product-edit-form"
                                        enctype="multipart/form-data"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <input
                                            type="hidden"
                                            name="_product_id"
                                            value="{{ $product->id }}"
                                        >

                                        <h3>Edit {{ $product->name }}</h3>

                                        <div class="fields">
                                            <div class="field">
                                                <label for="product_name_{{ $product->id }}">
                                                    Product Name
                                                </label>
                                                <input
                                                    id="product_name_{{ $product->id }}"
                                                    type="text"
                                                    name="name"
                                                    maxlength="255"
                                                    value="{{ $useOldProductInput ? old('name', $product->name) : $product->name }}"
                                                    required
                                                >
                                            </div>

                                            <div class="field">
                                                <label for="inventory_type_{{ $product->id }}">
                                                    Inventory Type
                                                </label>
                                                <select id="inventory_type_{{ $product->id }}" disabled>
                                                    <option value="rice" {{ ($product->inventory_type ?? 'rice') === 'rice' ? 'selected' : '' }}>Rice</option>
                                                    <option value="generic" {{ ($product->inventory_type ?? 'rice') === 'generic' ? 'selected' : '' }}>Generic Product</option>
                                                </select>
                                                <small class="muted">
                                                    Inventory type is locked to preserve existing stock records.
                                                </small>
                                            </div>

                                            <div class="field">
                                                <label for="description_{{ $product->id }}">
                                                    Description
                                                </label>
                                                <textarea id="description_{{ $product->id }}" name="description">{{ $useOldProductInput ? old('description', $product->description) : $product->description }}</textarea>
                                            </div>

                                            <div class="field">
                                                <label for="image_{{ $product->id }}">Product Image</label>

                                                <img
                                                    src="{{ $product->image
                                                        ? asset('storage/' . ltrim($product->image, '/'))
                                                        : asset('images/default-product.svg') }}"
                                                    alt="{{ $product->name }}"
                                                    class="edit-image-preview"
                                                    onerror="this.onerror=null;this.src='{{ asset('images/default-product.svg') }}';"
                                                >

                                                <input
                                                    id="image_{{ $product->id }}"
                                                    class="edit-image-input"
                                                    type="file"
                                                    name="image"
                                                    accept="image/*"
                                                >

                                                <small class="muted">
                                                    Optional. Upload any supported image format to replace the current one. Maximum 10 MB.
                                                </small>
                                            </div>

                                            @if (($product->inventory_type ?? 'rice') === 'generic')
                                                <div class="selling-units-editor">
                                                    <h4>Selling Units &amp; Prices</h4>
                                                    <p class="muted">I-update ang conversion, presyo at cost ng bawat unit. Dapat manatiling aktibo ang base unit na may factor na 1.</p>
                                                    <div class="selling-unit-list" data-product-id="{{ $product->id }}">
                                                        @foreach ($product->sellingUnits as $unitIndex => $sellingUnit)
                                                            <div class="selling-unit-row" data-unit-row>
                                                                <input type="hidden" name="selling_units[{{ $unitIndex }}][id]" value="{{ $sellingUnit->id }}">
                                                                <div class="field">
                                                                    <label>Unit name</label>
                                                                    <input name="selling_units[{{ $unitIndex }}][unit_name]" value="{{ $sellingUnit->unit_name }}" maxlength="30" required @readonly(strtolower($sellingUnit->unit_name) === strtolower($product->base_unit ?: $product->stock_unit))>
                                                                </div>
                                                                <div class="field">
                                                                    <label>Base units per unit</label>
                                                                    <input name="selling_units[{{ $unitIndex }}][conversion_factor]" type="number" min="0.001" max="1000000" step="0.001" value="{{ $sellingUnit->conversion_factor }}" required @readonly(strtolower($sellingUnit->unit_name) === strtolower($product->base_unit ?: $product->stock_unit))>
                                                                </div>
                                                                <div class="field">
                                                                    <label>Selling price (₱)</label>
                                                                    <input name="selling_units[{{ $unitIndex }}][selling_price]" type="number" min="0.01" max="9999999.99" step="0.01" value="{{ $sellingUnit->selling_price }}" required>
                                                                </div>
                                                                <div class="field">
                                                                    <label>Cost (₱)</label>
                                                                    <input name="selling_units[{{ $unitIndex }}][cost_price]" type="number" min="0" max="9999999.99" step="0.01" value="{{ $sellingUnit->cost_price }}">
                                                                </div>
                                                                <label class="selling-unit-active">
                                                                    @if (strtolower($sellingUnit->unit_name) === strtolower($product->base_unit ?: $product->stock_unit))
                                                                        <input type="hidden" name="selling_units[{{ $unitIndex }}][is_active]" value="1">
                                                                    @endif
                                                                    <input type="checkbox" name="selling_units[{{ $unitIndex }}][is_active]" value="1" @checked($sellingUnit->is_active) @disabled(strtolower($sellingUnit->unit_name) === strtolower($product->base_unit ?: $product->stock_unit))>
                                                                    Active
                                                                </label>
                                                                @if (strtolower($sellingUnit->unit_name) === strtolower($product->base_unit ?: $product->stock_unit))
                                                                    <span class="muted">Base unit</span>
                                                                @else
                                                                    <button type="button" class="button selling-unit-remove" data-remove-selling-unit>Remove</button>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <button type="button" class="button button-edit add-selling-unit" data-add-selling-unit="{{ $product->id }}">+ Add Selling Unit</button>
                                                </div>

                                                <div class="field">
                                                    <label for="low_stock_threshold_quantity_{{ $product->id }}">
                                                        Low Stock Threshold ({{ $product->base_unit ?: $product->stock_unit }})
                                                    </label>
                                                    <input
                                                        id="low_stock_threshold_quantity_{{ $product->id }}"
                                                        type="number"
                                                        name="low_stock_threshold_quantity"
                                                        min="0"
                                                        max="1000000"
                                                        step="0.01"
                                                        value="{{ $useOldProductInput ? old('low_stock_threshold_quantity', $product->low_stock_threshold_quantity ?? 20) : ($product->low_stock_threshold_quantity ?? 20) }}"
                                                        required
                                                    >
                                                </div>
                                            @else
                                                <div class="field">
                                                    <label for="price_per_kg_{{ $product->id }}">
                                                        Price per Kg (₱)
                                                    </label>
                                                    <input
                                                        id="price_per_kg_{{ $product->id }}"
                                                        type="number"
                                                        name="price_per_kg"
                                                        min="0"
                                                        max="9999999.99"
                                                        step="0.01"
                                                        value="{{ $useOldProductInput ? old('price_per_kg', $product->price_per_kg) : $product->price_per_kg }}"
                                                    >
                                                </div>

                                                <div class="field">
                                                    <label for="price_per_sack_{{ $product->id }}">
                                                        Price per Sack (₱)
                                                    </label>
                                                    <input
                                                        id="price_per_sack_{{ $product->id }}"
                                                        type="number"
                                                        name="price_per_sack"
                                                        min="0"
                                                        max="9999999.99"
                                                        step="0.01"
                                                        value="{{ $useOldProductInput ? old('price_per_sack', $product->price_per_sack) : $product->price_per_sack }}"
                                                    >
                                                </div>

                                                <div class="field">
                                                    <label for="sack_weight_kg_{{ $product->id }}">
                                                        Sack Weight (Kg)
                                                    </label>
                                                    <input
                                                        id="sack_weight_kg_{{ $product->id }}"
                                                        type="number"
                                                        name="sack_weight_kg"
                                                        min="1"
                                                        max="1000"
                                                        step="1"
                                                        value="{{ $useOldProductInput ? old('sack_weight_kg', $product->sack_weight_kg) : $product->sack_weight_kg }}"
                                                        required
                                                    >
                                                </div>

                                                <div class="field">
                                                    <label for="cost_per_sack_{{ $product->id }}">
                                                        Cost per Sack (₱)
                                                    </label>
                                                    <input
                                                        id="cost_per_sack_{{ $product->id }}"
                                                        type="number"
                                                        name="cost_per_sack"
                                                        min="0"
                                                        max="9999999.99"
                                                        step="0.01"
                                                        value="{{ $useOldProductInput ? old('cost_per_sack', $product->cost_per_sack) : $product->cost_per_sack }}"
                                                    >
                                                </div>

                                                <div class="field">
                                                    <label for="profit_percentage_{{ $product->id }}">
                                                        Target Profit (%)
                                                    </label>
                                                    <input
                                                        id="profit_percentage_{{ $product->id }}"
                                                        type="number"
                                                        name="profit_percentage"
                                                        min="0"
                                                        max="100"
                                                        step="0.01"
                                                        value="{{ $useOldProductInput ? old('profit_percentage', $product->profit_percentage ?? 15) : ($product->profit_percentage ?? 15) }}"
                                                    >
                                                </div>

                                                <div class="field">
                                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                                        <h5 class="mb-0">Low Stock Alert (Sacks)</h5>

                                                        <div id="lowStockQrCode-{{ $product->id }}" class="flex-shrink-0"></div>
                                                    </div>
                                                    <label for="low_stock_threshold_sacks_{{ $product->id }}">
                                                        Low Stock Alert (Sacks)
                                                    </label>
                                                    <input
                                                        id="low_stock_threshold_sacks_{{ $product->id }}"
                                                        type="number"
                                                        name="low_stock_threshold_sacks"
                                                        min="0"
                                                        max="1000000"
                                                        step="1"
                                                        value="{{ $useOldProductInput
                                                            ? old(
                                                                'low_stock_threshold_sacks',
                                                                $product->sack_weight_kg > 0
                                                                    ? floor(($product->low_stock_threshold ?? 0) / $product->sack_weight_kg)
                                                                    : 0
                                                            )
                                                            : ($product->sack_weight_kg > 0
                                                                ? floor(($product->low_stock_threshold ?? 0) / $product->sack_weight_kg)
                                                                : 0) }}"
                                                    >
                                                </div>
                                            @endif
                                        </div>

                                        <div class="field checkbox-field">
                                            <label for="price_confirmed_{{ $product->id }}">
                                                <input type="hidden" name="price_confirmed" value="0">
                                                <input
                                                    id="price_confirmed_{{ $product->id }}"
                                                    type="checkbox"
                                                    name="price_confirmed"
                                                    value="1"
                                                    @checked($useOldProductInput ? old('price_confirmed', $product->price_confirmed) : $product->price_confirmed)
                                                >
                                                <span>Confirm product pricing</span>
                                            </label>
                                        </div>

                                        <div class="field checkbox-field">
                                            <label for="is_active_{{ $product->id }}">
                                                <input type="hidden" name="is_active" value="0">
                                                <input
                                                    id="is_active_{{ $product->id }}"
                                                    type="checkbox"
                                                    name="is_active"
                                                    value="1"
                                                    @checked($useOldProductInput ? old('is_active', $product->is_active) : $product->is_active)
                                                >
                                                <span>Active</span>
                                            </label>
                                        </div>

                                        <div class="edit-actions">
                                            <button type="submit" class="button">
                                                Save Changes
                                            </button>

                                            <button
                                                type="button"
                                                class="button button-cancel"
                                                onclick="toggleProductForm({{ $product->id }}, null)"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </form>

                                    <form
                                    method="POST"
                                    action="{{ route('admin.inventory.adjustment') }}"
                                    class="quick-stock-adjustment"
                                    >
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="return_to" value="products">
                                    <h3>Quick Stock Adjustment</h3>
                                    <div class="quick-stock-current">
                                        Current: {{ number_format($productStock, 2) }} {{ $productUnit }}
                                    </div>
                                    <div class="field">
                                        <label>Adjustment</label>
                                        <select name="adjustment_type" required>
                                            <option value="adjustment_in">Add stock</option>
                                            <option value="adjustment_out">Remove stock</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Quantity</label>
                                        <input name="quantity" type="number" min="0.01" max="1000000" step="0.01" required>
                                    </div>
                                    <div class="field">
                                        <label>Unit</label>
                                        <select name="unit" class="quick-stock-unit" required>
                                            @if ($isGenericProduct)
                                                @foreach ($product->sellingUnits->where('is_active', true) as $sellingUnit)
                                                    <option value="{{ strtolower($sellingUnit->unit_name) }}">{{ $sellingUnit->unit_name }}</option>
                                                @endforeach
                                            @else
                                                <option value="kg">kg</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Reason</label>
                                        <select name="reason" required>
                                            <option value="physical_count">Physical count</option>
                                            <option value="damage">Damage</option>
                                            <option value="wastage">Wastage</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="field">
                                        <label>Notes (optional)</label>
                                        <textarea name="notes" rows="2" maxlength="1500"></textarea>
                                    </div>
                                    <input type="hidden" name="movement_date" value="{{ now()->toDateString() }}">
                                    <button class="button" type="submit">Record Adjustment</button>
                                    </form>

                                    <div class="product-qr-section">
                                        <h4>Product QR Code</h4>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.products.qr-code.update', $product) }}"
                                        >
                                            @csrf
                                            @method('PUT')

                                            <div class="field">
                                                <label for="qr_code_{{ $product->id }}">
                                                    QR Identifier
                                                </label>

                                                <input
                                                    id="qr_code_{{ $product->id }}"
                                                    name="qr_code"
                                                    type="text"
                                                    maxlength="255"
                                                    value="{{ $product->qr_code }}"
                                                    placeholder="Enter a unique product QR identifier"
                                                    required
                                                >

                                                <small class="muted">
                                                    Dapat natatangi ang identifier ng bawat produkto.
                                                    Hindi ito delivery reference.
                                                </small>
                                            </div>

                                            <button type="submit" class="button">
                                                Save QR Identifier
                                            </button>
                                        </form>

                                        @if ($product->qr_code)
                                            <p>
                                                <strong>Current identifier:</strong>
                                                {{ $product->qr_code }}
                                            </p>
                                            <div class="product-qr-image">
                                                <div
                                                    id="qr-image-{{ $product->id }}"
                                                    class="qr-image-container"
                                                    data-qr-code="{{ $product->qr_code }}"
                                                    data-product-name="{{ $product->name }}"
                                                ></div>

                                                <button
                                                    type="button"
                                                    class="button"
                                                    onclick="printProductQr({{ $product->id }})"
                                                >
                                                    Print QR Code
                                                </button>

                                                <p class="muted">
                                                    I-scan ang QR code para mahanap ang produktong ito sa Stock In.
                                                </p>
                                            </div>
                                        @else
                                            <p class="muted">
                                                Wala pang QR identifier ang produktong ito.
                                            </p>
                                        @endif
                                    </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <strong>No products found</strong>
                <span>No products are currently available to display.</span>
            </div>
        @endif
    </section>

    <dialog class="product-edit-modal" id="productEditModal" aria-labelledby="productEditModalTitle">
        <div class="product-edit-modal-header">
            <h2 id="productEditModalTitle">Edit Product</h2>
            <button type="button" class="product-edit-modal-close" aria-label="Close edit dialog" onclick="closeProductModal()">×</button>
        </div>
        <div class="product-edit-modal-body" id="productEditModalBody"></div>
    </dialog>
</div>

<script>
    function toggleProductForm(productId, button) {
        const row = document.getElementById('product-form-row-' + productId);
        const content = document.getElementById('product-edit-content-' + productId);
        const modal = document.getElementById('productEditModal');

        if (!row || !content || !modal) {
            return;
        }

        if (modal.open) {
            modal.close();
        }

        modal.dataset.productId = productId;
        document.getElementById('productEditModalTitle').textContent =
            'Edit ' + (row.previousElementSibling.querySelector('.product-name span')?.textContent.trim() || 'Product');
        document.getElementById('productEditModalBody').append(content);
        modal.showModal();
        modal.querySelector('.product-edit-modal-close').focus();
    }

    function closeProductModal() {
        const modal = document.getElementById('productEditModal');

        if (modal?.open) {
            modal.close();
        }
    }

    function calculateSuggestedPrice(productId) {
        const costInput = document.getElementById(
            'cost_per_sack_' + productId
        );

        const profitInput = document.getElementById(
            'profit_percentage_' + productId
        );

        const weightInput = document.getElementById(
            'sack_weight_kg_' + productId
        );

        const output = document.getElementById(
            'suggested-price-' + productId
        );

        if (!costInput || !profitInput || !weightInput || !output) {
            return;
        }

        const cost = Number(costInput.value);
        const profit = Number(profitInput.value);
        const weight = Number(weightInput.value);

        output.hidden = false;

        if (
            costInput.value === '' ||
            profitInput.value === '' ||
            weightInput.value === '' ||
            !Number.isFinite(cost) ||
            !Number.isFinite(profit) ||
            !Number.isFinite(weight) ||
            cost < 0 ||
            profit < 0 ||
            weight <= 0
        ) {
            output.textContent =
                'Enter a valid cost, target profit percentage, and sack weight.';
            return;
        }

        const suggestedSackPrice = cost * (1 + profit / 100);
        const suggestedKgPrice = suggestedSackPrice / weight;

        const money = value => value.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        output.textContent =
            'Suggested price per sack: ' + money(suggestedSackPrice) +
            ' | Suggested price per kg: ' + money(suggestedKgPrice);
    }

    function filterProducts() {
        const search = document
            .getElementById('productSearch')
            .value
            .trim()
            .toLowerCase();

        const status = document.getElementById('stockFilter').value;
        const category = document.getElementById('categoryFilter').value;

        const rows = document.querySelectorAll(
            '.products-page tr.product-data-row'
        );

        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.dataset.productName || '';
            const stockStatus = row.dataset.stockStatus || '';
            const inventoryType = row.dataset.inventoryType || '';
            const productCategory = row.dataset.category || 'uncategorized';
            const matchesSearch = name.includes(search);
            const matchesStatus = status === 'all' || stockStatus === status;
            const matchesCategory = category === 'all'
                || category === inventoryType
                || category === 'category:' + productCategory;
            const show = matchesSearch && matchesStatus && matchesCategory;

            row.hidden = !show;
            if (!show) {
                row.querySelector('.product-select-checkbox').checked = false;
            }

            const editRow = row.nextElementSibling;

            if (editRow && editRow.classList.contains('edit-form-row')) {
                if (!show) {
                    editRow.style.display = 'none';

                    const editButton = row.querySelector('[aria-expanded]');

                    if (editButton) {
                        editButton.setAttribute('aria-expanded', 'false');
                    }
                }
            }

            visibleCount += show ? 1 : 0;
        });

        document.getElementById('filterResultCount').textContent =
            visibleCount + (visibleCount === 1 ? ' product' : ' products');

        updateBulkSelection();
    }

    function updateBulkSelection() {
        const selected = document.querySelectorAll(
            '.product-select-checkbox:checked'
        ).length;
        const bulkActions = document.getElementById('bulkActions');

        document.getElementById('selectedProductCount').textContent = selected;
        bulkActions.hidden = selected === 0;
    }

    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('productEditModal');

        modal.addEventListener('close', function () {
            const productId = modal.dataset.productId;
            const content = document.getElementById(
                'product-edit-content-' + productId
            );
            const row = document.getElementById(
                'product-form-row-' + productId
            );

            if (content && row) {
                row.querySelector('td').append(content);
            }

            const editButton = document.querySelector(
                '[aria-controls="product-form-row-' + productId + '"]'
            );

            if (editButton) {
                editButton.setAttribute('aria-expanded', 'false');
            }

            delete modal.dataset.productId;
        });

        document.querySelectorAll('.button-edit[aria-controls]').forEach(button => {
            button.addEventListener('click', function () {
                button.setAttribute('aria-expanded', 'true');
            });
        });

        document.querySelectorAll('.product-select-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkSelection);
        });

        document.getElementById('selectAllProducts')?.addEventListener(
            'change',
            function () {
                document.querySelectorAll('.product-data-row').forEach(row => {
                    if (!row.hidden) {
                        row.querySelector('.product-select-checkbox').checked =
                            this.checked;
                    }
                });
                updateBulkSelection();
            }
        );

        document.getElementById('bulkProductsForm')?.addEventListener(
            'submit',
            function (event) {
                if (!document.querySelector('.product-select-checkbox:checked')) {
                    event.preventDefault();
                    alert('Select at least one product first.');
                    return;
                }

                if (
                    document.getElementById('bulkProductAction').value === 'remove'
                    && !confirm(
                        'Remove the selected products? Products with order history will be deactivated; products without order history will be permanently deleted.'
                    )
                ) {
                    event.preventDefault();
                }
            }
        );

        document.querySelectorAll('.edit-image-input').forEach(input => {
            input.addEventListener('change', function () {
                const file = this.files?.[0];
                const preview = this.closest('.field')?.querySelector(
                    '.edit-image-preview'
                );

                if (!file || !preview) {
                    return;
                }

                if (preview.dataset.previewUrl) {
                    URL.revokeObjectURL(preview.dataset.previewUrl);
                }

                const previewUrl = URL.createObjectURL(file);
                preview.dataset.previewUrl = previewUrl;
                preview.src = previewUrl;
            });
        });

        document.querySelectorAll('[data-add-selling-unit]').forEach(button => {
            button.addEventListener('click', function () {
                const productId = this.dataset.addSellingUnit;
                const list = document.querySelector(
                    '.selling-unit-list[data-product-id="' + productId + '"]'
                );
                const form = document.getElementById(
                    'product-edit-content-' + productId
                ).querySelector('.product-edit-form');
                const index = Number(form.dataset.nextUnitIndex || 0);

                form.dataset.nextUnitIndex = index + 1;
                list.insertAdjacentHTML('beforeend', `
                    <div class="selling-unit-row" data-unit-row>
                        <div class="field">
                            <label>Unit name</label>
                            <input name="selling_units[${index}][unit_name]" maxlength="30" required>
                        </div>
                        <div class="field">
                            <label>Base units per unit</label>
                            <input name="selling_units[${index}][conversion_factor]" type="number" min="0.001" max="1000000" step="0.001" required>
                        </div>
                        <div class="field">
                            <label>Selling price (₱)</label>
                            <input name="selling_units[${index}][selling_price]" type="number" min="0.01" max="9999999.99" step="0.01" required>
                        </div>
                        <div class="field">
                            <label>Cost (₱)</label>
                            <input name="selling_units[${index}][cost_price]" type="number" min="0" max="9999999.99" step="0.01">
                        </div>
                        <label class="selling-unit-active">
                            <input type="checkbox" name="selling_units[${index}][is_active]" value="1" checked>
                            Active
                        </label>
                        <button type="button" class="button selling-unit-remove" data-remove-selling-unit>Remove</button>
                    </div>
                `);
            });
        });

        document.addEventListener('click', function (event) {
            if (event.target.closest('[data-remove-selling-unit]')) {
                event.target.closest('[data-unit-row]').remove();
            }
        });

        document.querySelectorAll('.product-edit-form').forEach(form => {
            const indexes = [...form.querySelectorAll(
                '[name^="selling_units["]'
            )].map(input => {
                const match = input.name.match(/^selling_units\[(\d+)\]/);
                return match ? Number(match[1]) : -1;
            });
            form.dataset.nextUnitIndex =
                indexes.length ? Math.max(...indexes) + 1 : 0;
        });

        document.querySelectorAll('.quick-stock-adjustment').forEach(form => {
            const productRow = document.querySelector(
                '.product-data-row[data-product-id="' +
                form.querySelector('[name="product_id"]').value +
                '"]'
            );
            const unitSelect = form.querySelector('.quick-stock-unit');

            if (productRow?.dataset.inventoryType === 'generic') {
                try {
                    const units = JSON.parse(productRow.dataset.sellingUnits || '[]')
                        .filter(unit => unit.is_active);

                    unitSelect.replaceChildren(...units.map(unit => new Option(
                        unit.unit_name,
                        unit.unit_name.toLowerCase()
                    )));
                } catch (error) {
                    console.error('Unable to load configured selling units.', error);
                }
            }
        });

        const failedProductId = @json(old('_product_id'));

        if (failedProductId) {
            toggleProductForm(failedProductId, null);
        }
    });
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof QRCode === 'undefined') {
            console.error('QR generator library did not load.');
            return;
        }

        document.querySelectorAll('.qr-image-container').forEach(function (container) {
            const code = container.dataset.qrCode;

            if (!code) return;

            new QRCode(container, {
                text: code,
                width: 200,
                height: 200,
                correctLevel: QRCode.CorrectLevel.M
            });
        });
    });

    function printProductQr(productId) {
        const container = document.getElementById('qr-image-' + productId);

        if (!container || !container.dataset.qrCode) {
            alert('Walang QR identifier ang produktong ito.');
            return;
        }

        const qrImage = container.querySelector('img');
        const qrCanvas = container.querySelector('canvas');
        const imageUrl = qrImage ? qrImage.src :
            (qrCanvas ? qrCanvas.toDataURL('image/png') : null);

        if (!imageUrl) {
            alert('Hindi pa nalilikha ang QR image. Tingnan ang internet connection at subukan muli.');
            return;
        }

        const productName = container.dataset.productName;
        const printWindow = window.open('', '_blank');

        if (!printWindow) {
            alert('Payagan ang pop-ups upang ma-print ang QR code.');
            return;
        }

        printWindow.document.write(`
            <!doctype html>
            <html>
            <head>
                <title>Product QR Code</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 24px; }
                    img { width: 240px; height: 240px; }
                    h2 { font-size: 20px; }
                    p { overflow-wrap: anywhere; }
                    @media print { button { display: none; } }
                </style>
            </head>
            <body>
                <h2>${escapeHtml(productName)}</h2>
                <img src="${imageUrl}" alt="Product QR Code">
                <p>${escapeHtml(container.dataset.qrCode)}</p>
                <button onclick="window.print()">Print</button>
            </body>
            </html>
        `);

        printWindow.document.close();
    }

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (character) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[character];
        });
    }
</script>

@endsection
