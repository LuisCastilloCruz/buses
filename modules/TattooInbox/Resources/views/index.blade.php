@extends('tenant.layouts.app')

@push('styles')
    <style>
        .tattoo-inbox-page,
        .tattoo-inbox-page * {
            box-sizing: border-box;
        }

        .tattoo-inbox-page {
            padding: 24px !important;
            background: linear-gradient(180deg, #f6f8fc 0%, #eef3f9 100%) !important;
            min-height: calc(100vh - 90px) !important;
        }

        .tattoo-inbox-page .ti-shell {
            border: 0 !important;
            border-radius: 24px !important;
            overflow: hidden !important;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.10) !important;
            background: #ffffff !important;
        }

        .tattoo-inbox-page .ti-topbar {
            background:
                radial-gradient(circle at top left, rgba(37, 211, 102, 0.10), transparent 28%),
                radial-gradient(circle at top right, rgba(225, 48, 108, 0.10), transparent 30%),
                linear-gradient(135deg, #ffffff 0%, #f8fbff 100%) !important;
            border-bottom: 1px solid #edf2f7 !important;
            padding: 24px !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            gap: 16px !important;
            flex-wrap: wrap !important;
        }

        .tattoo-inbox-page .ti-title-wrap {
            display: flex !important;
            align-items: center !important;
            gap: 16px !important;
            min-width: 0 !important;
        }

        .tattoo-inbox-page .ti-brand-icons {
            display: flex !important;
            gap: 12px !important;
            flex-shrink: 0 !important;
        }

        .tattoo-inbox-page .ti-brand-icon {
            width: 54px !important;
            height: 54px !important;
            border-radius: 16px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #fff !important;
            font-size: 24px !important;
            box-shadow: 0 12px 24px rgba(0, 0, 0, .10) !important;
        }

        .tattoo-inbox-page .ti-brand-icon.whatsapp {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%) !important;
        }

        .tattoo-inbox-page .ti-brand-icon.instagram {
            background: linear-gradient(135deg, #F58529 0%, #DD2A7B 45%, #8134AF 70%, #515BD4 100%) !important;
        }

        .tattoo-inbox-page .ti-main-title {
            margin: 0 0 4px 0 !important;
            font-size: 34px !important;
            line-height: 1.1 !important;
            font-weight: 800 !important;
            color: #111827 !important;
        }

        .tattoo-inbox-page .ti-main-subtitle {
            margin: 0 !important;
            color: #6b7280 !important;
            font-size: 15px !important;
            line-height: 1.5 !important;
        }

        .tattoo-inbox-page .ti-refresh-btn {
            border: 1px solid #111827 !important;
            background: #fff !important;
            color: #111827 !important;
            border-radius: 14px !important;
            padding: 12px 18px !important;
            font-weight: 700 !important;
            min-height: 46px !important;
            outline: none !important;
            box-shadow: none !important;
            transition: all .16s ease !important;
        }

        .tattoo-inbox-page .ti-refresh-btn:hover {
            background: #111827 !important;
            color: #fff !important;
        }

        .tattoo-inbox-page .ti-summary-wrap {
            padding: 24px !important;
            background: #f8fbff !important;
            border-bottom: 1px solid #edf2f7 !important;
        }

        .tattoo-inbox-page .ti-summary-row,
        .tattoo-inbox-page .ti-content-row {
            display: flex !important;
            flex-wrap: wrap !important;
            margin-left: -12px !important;
            margin-right: -12px !important;
        }

        .tattoo-inbox-page .ti-col {
            padding-left: 12px !important;
            padding-right: 12px !important;
            margin-bottom: 24px !important;
        }

        .tattoo-inbox-page .ti-col-3 {
            width: 25% !important;
        }

        .tattoo-inbox-page .ti-col-left {
            width: 28% !important;
        }

        .tattoo-inbox-page .ti-col-center {
            width: 44% !important;
        }

        .tattoo-inbox-page .ti-col-right {
            width: 28% !important;
        }

        .tattoo-inbox-page .ti-summary-card {
            border: 0 !important;
            border-radius: 18px !important;
            color: #fff !important;
            height: 100% !important;
            position: relative !important;
            overflow: hidden !important;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.10) !important;
            padding: 20px !important;
        }

        .tattoo-inbox-page .ti-summary-card::after {
            content: '' !important;
            position: absolute !important;
            right: -20px !important;
            top: -20px !important;
            width: 90px !important;
            height: 90px !important;
            border-radius: 999px !important;
            background: rgba(255, 255, 255, .12) !important;
        }

        .tattoo-inbox-page .ti-summary-card.total {
            background: linear-gradient(135deg, #111827 0%, #374151 100%) !important;
        }

        .tattoo-inbox-page .ti-summary-card.leads {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
        }

        .tattoo-inbox-page .ti-summary-card.open {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        }

        .tattoo-inbox-page .ti-summary-card.quoted {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        }

        .tattoo-inbox-page .ti-summary-label {
            font-size: 12px !important;
            color: rgba(255, 255, 255, .88) !important;
            margin-bottom: 8px !important;
            text-transform: uppercase !important;
            letter-spacing: .08em !important;
            font-weight: 700 !important;
        }

        .tattoo-inbox-page .ti-summary-value {
            font-size: 32px !important;
            font-weight: 800 !important;
            line-height: 1 !important;
            margin: 0 0 8px 0 !important;
            color: #fff !important;
        }

        .tattoo-inbox-page .ti-summary-help {
            font-size: 12px !important;
            opacity: .92 !important;
            color: #fff !important;
        }

        .tattoo-inbox-page .ti-content-wrap {
            padding: 24px !important;
        }

        .tattoo-inbox-page .ti-panel {
            border: 1px solid #e8edf5 !important;
            border-radius: 20px !important;
            background: #fff !important;
            overflow: hidden !important;
            height: 100% !important;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.05) !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 690px !important;
        }

        .tattoo-inbox-page .ti-panel-header {
            padding: 16px !important;
            border-bottom: 1px solid #edf2f7 !important;
            background: linear-gradient(180deg, #ffffff 0%, #fbfcfe 100%) !important;
        }

        .tattoo-inbox-page .ti-panel-title {
            font-size: 16px !important;
            font-weight: 800 !important;
            color: #111827 !important;
            margin: 0 0 4px 0 !important;
            line-height: 1.3 !important;
        }

        .tattoo-inbox-page .ti-panel-subtitle {
            font-size: 12px !important;
            color: #6b7280 !important;
            margin: 0 !important;
            line-height: 1.4 !important;
        }

        .tattoo-inbox-page .ti-sticky-toolbar {
            position: sticky !important;
            top: 0 !important;
            z-index: 2 !important;
            background: #fff !important;
            padding: 16px !important;
            border-bottom: 1px solid #edf2f7 !important;
        }

        .tattoo-inbox-page .ti-input,
        .tattoo-inbox-page .ti-select,
        .tattoo-inbox-page .ti-textarea {
            width: 100% !important;
            border: 1px solid #dbe3ef !important;
            border-radius: 12px !important;
            min-height: 44px !important;
            background: #fff !important;
            color: #111827 !important;
            padding: 10px 14px !important;
            font-size: 14px !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .tattoo-inbox-page .ti-input::placeholder,
        .tattoo-inbox-page .ti-textarea::placeholder {
            color: #9ca3af !important;
        }

        .tattoo-inbox-page .ti-textarea {
            min-height: 54px !important;
            resize: none !important;
            border-radius: 14px 0 0 14px !important;
            border-right: 0 !important;
        }

        .tattoo-inbox-page .ti-filter-grid {
            display: flex !important;
            gap: 12px !important;
            margin-top: 12px !important;
        }

        .tattoo-inbox-page .ti-filter-grid>div {
            flex: 1 1 0 !important;
        }

        .tattoo-inbox-page .ti-summary-clickable {
            cursor: pointer !important;
            transition: transform .16s ease, box-shadow .16s ease, opacity .16s ease !important;
        }

        .tattoo-inbox-page .ti-summary-clickable:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.14) !important;
        }

        .tattoo-inbox-page .ti-summary-clickable.is-active {
            outline: 3px solid rgba(255, 255, 255, 0.35) !important;
            outline-offset: -3px !important;
        }

        .tattoo-inbox-page .ti-conversation-list {
            flex: 1 1 auto !important;
            max-height: 690px !important;
            overflow-y: auto !important;
            background: #fff !important;
            padding: 8px !important;
        }

        .tattoo-inbox-page .ti-conversation-item {
            display: block !important;
            width: 100% !important;
            border: 1px solid transparent !important;
            background: #fff !important;
            text-align: left !important;
            padding: 16px !important;
            transition: all .16s ease !important;
            outline: 0 !important;
            position: relative !important;
            cursor: pointer !important;
            border-radius: 16px !important;
            margin-bottom: 10px !important;
            appearance: none !important;
            -webkit-appearance: none !important;
            box-shadow: none !important;
        }

        .tattoo-inbox-page .ti-conversation-item:last-child {
            margin-bottom: 0 !important;
        }

        .tattoo-inbox-page .ti-conversation-item:hover {
            background: #f8fbff !important;
            transform: translateY(-1px) !important;
            border-color: #e5edf8 !important;
        }

        .tattoo-inbox-page .ti-conversation-item.active {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(124, 58, 237, 0.08) 100%) !important;
            border: 1px solid rgba(59, 130, 246, .18) !important;
            box-shadow: 0 14px 30px rgba(59, 130, 246, .10) !important;
        }

        .tattoo-inbox-page .ti-conversation-item.single-mode {
            margin: 16px !important;
            padding: 24px !important;
            border: 1px solid #e5e7eb !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, .08) !important;
            background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%) !important;
            transform: none !important;
        }

        .tattoo-inbox-page .ti-conversation-item.single-mode:hover {
            transform: translateY(-2px) !important;
        }

        .tattoo-inbox-page .ti-conversation-item.single-mode .ti-conversation-name {
            font-size: 18px !important;
        }

        .tattoo-inbox-page .ti-conversation-item.single-mode .ti-conversation-preview {
            font-size: 14px !important;
        }

        .tattoo-inbox-page .ti-conversation-item.single-mode .ti-price-highlight {
            font-size: 14px !important;
            padding: 10px 14px !important;
        }

        .tattoo-inbox-page .ti-conversation-top {
            display: flex !important;
            justify-content: space-between !important;
            gap: 12px !important;
            margin-bottom: 8px !important;
        }

        .tattoo-inbox-page .ti-conversation-left {
            display: flex !important;
            gap: 12px !important;
            min-width: 0 !important;
            flex: 1 1 auto !important;
        }

        .tattoo-inbox-page .ti-avatar,
        .tattoo-inbox-page .ti-chat-avatar {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #fff !important;
            box-shadow: 0 10px 20px rgba(0, 0, 0, .08) !important;
            flex-shrink: 0 !important;
        }

        .tattoo-inbox-page .ti-avatar {
            width: 48px !important;
            height: 48px !important;
            min-width: 48px !important;
            border-radius: 14px !important;
            font-size: 16px !important;
        }

        .tattoo-inbox-page .ti-chat-avatar {
            width: 54px !important;
            height: 54px !important;
            min-width: 54px !important;
            border-radius: 16px !important;
            font-size: 18px !important;
        }

        .tattoo-inbox-page .ti-avatar.whatsapp,
        .tattoo-inbox-page .ti-chat-avatar.whatsapp {
            background: linear-gradient(135deg, #25D366 0%, #128C7E 100%) !important;
        }

        .tattoo-inbox-page .ti-avatar.instagram,
        .tattoo-inbox-page .ti-chat-avatar.instagram {
            background: linear-gradient(135deg, #F58529 0%, #DD2A7B 45%, #8134AF 70%, #515BD4 100%) !important;
        }

        .tattoo-inbox-page .ti-conversation-name {
            font-size: 14px !important;
            font-weight: 800 !important;
            color: #111827 !important;
            margin: 0 0 3px 0 !important;
            line-height: 1.25 !important;
        }

        .tattoo-inbox-page .ti-conversation-phone,
        .tattoo-inbox-page .ti-conversation-date {
            font-size: 12px !important;
            color: #6b7280 !important;
        }

        .tattoo-inbox-page .ti-conversation-date {
            white-space: nowrap !important;
            flex-shrink: 0 !important;
            text-align: right !important;
        }

        .tattoo-inbox-page .ti-conversation-preview {
            font-size: 12.5px !important;
            color: #4b5563 !important;
            line-height: 1.45 !important;
            margin-top: 8px !important;
        }

        .tattoo-inbox-page .ti-price-highlight {
            display: inline-block !important;
            margin-top: 8px !important;
            padding: 8px 12px !important;
            border-radius: 12px !important;
            background: #f8fafc !important;
            color: #111827 !important;
            font-weight: 800 !important;
            font-size: 12px !important;
            border: 1px solid #e5e7eb !important;
        }

        .tattoo-inbox-page .ti-chip-row {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
            margin-top: 8px !important;
        }

        .tattoo-inbox-page .ti-badge {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            border-radius: 999px !important;
            padding: 6px 10px !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            letter-spacing: .02em !important;
            line-height: 1 !important;
        }

        .tattoo-inbox-page .ti-badge.whatsapp {
            background: rgba(37, 211, 102, 0.14) !important;
            color: #0f8f56 !important;
        }

        .tattoo-inbox-page .ti-badge.instagram {
            background: rgba(221, 42, 123, 0.12) !important;
            color: #b42368 !important;
        }

        .tattoo-inbox-page .ti-badge.quoted {
            background: rgba(16, 185, 129, 0.12) !important;
            color: #047857 !important;
        }

        .tattoo-inbox-page .ti-badge.pending_data {
            background: rgba(245, 158, 11, 0.16) !important;
            color: #b45309 !important;
        }

        .tattoo-inbox-page .ti-badge.open {
            background: rgba(37, 99, 235, 0.12) !important;
            color: #1d4ed8 !important;
        }

        .tattoo-inbox-page .ti-badge.closed {
            background: rgba(107, 114, 128, 0.14) !important;
            color: #4b5563 !important;
        }

        .tattoo-inbox-page .ti-chat-shell {
            display: flex !important;
            flex-direction: column !important;
            min-height: 690px !important;
            background: #fff !important;
            flex: 1 1 auto !important;
        }

        .tattoo-inbox-page .ti-chat-head {
            padding: 16px 18px !important;
            border-bottom: 1px solid #edf2f7 !important;
            background:
                radial-gradient(circle at left top, rgba(37, 211, 102, .08), transparent 26%),
                radial-gradient(circle at right top, rgba(221, 42, 123, .07), transparent 24%),
                #fff !important;
            min-height: 88px !important;
        }

        .tattoo-inbox-page .ti-chat-head-inner {
            display: flex !important;
            align-items: center !important;
            gap: 14px !important;
            min-width: 0 !important;
        }

        .tattoo-inbox-page .ti-chat-body {
            flex: 1 1 auto !important;
            min-height: 500px !important;
            max-height: 560px !important;
            overflow-y: auto !important;
            padding: 20px !important;
            background: linear-gradient(180deg, rgba(248, 250, 252, .95) 0%, rgba(241, 245, 249, .98) 100%) !important;
        }

        .tattoo-inbox-page .ti-chat-empty,
        .tattoo-inbox-page .ti-side-empty {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            color: #6b7280 !important;
            min-height: 250px !important;
            padding: 24px !important;
            font-weight: 600 !important;
        }

        .tattoo-inbox-page .ti-message-row {
            display: flex !important;
            margin-bottom: 16px !important;
        }

        .tattoo-inbox-page .ti-message-row.in {
            justify-content: flex-start !important;
        }

        .tattoo-inbox-page .ti-message-row.out {
            justify-content: flex-end !important;
        }

        .tattoo-inbox-page .ti-message-bubble {
            max-width: 80% !important;
            border-radius: 18px !important;
            padding: 14px 16px !important;
            font-size: 13px !important;
            line-height: 1.55 !important;
            box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08) !important;
            word-break: break-word !important;
            white-space: pre-wrap !important;
        }

        .tattoo-inbox-page .ti-message-row.in .ti-message-bubble {
            background: #fff !important;
            border: 1px solid #e5e7eb !important;
            color: #1f2937 !important;
            border-bottom-left-radius: 8px !important;
        }

        .tattoo-inbox-page .ti-message-row.out .ti-message-bubble {
            background: linear-gradient(135deg, #111827 0%, #374151 100%) !important;
            border: 1px solid #111827 !important;
            color: #fff !important;
            border-bottom-right-radius: 8px !important;
        }

        .tattoo-inbox-page .ti-message-meta {
            margin-top: 8px !important;
            font-size: 11px !important;
            opacity: .82 !important;
            font-weight: 600 !important;
        }

        .tattoo-inbox-page .ti-chat-footer {
            border-top: 1px solid #edf2f7 !important;
            background: #fff !important;
            padding: 16px !important;
        }

        .tattoo-inbox-page .ti-chat-form-row {
            display: flex !important;
            align-items: stretch !important;
        }

        .tattoo-inbox-page .ti-send-btn {
            min-width: 130px !important;
            border: 1px solid #111827 !important;
            background: #111827 !important;
            color: #fff !important;
            border-radius: 0 14px 14px 0 !important;
            font-weight: 700 !important;
            padding: 12px 16px !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .tattoo-inbox-page .ti-send-btn[disabled],
        .tattoo-inbox-page .ti-textarea[disabled] {
            opacity: .65 !important;
            cursor: not-allowed !important;
        }

        .tattoo-inbox-page .ti-side-body {
            flex: 1 1 auto !important;
            max-height: 690px !important;
            overflow-y: auto !important;
            padding: 16px !important;
            background: #fff !important;
        }

        .tattoo-inbox-page .ti-detail-section {
            border: 1px solid #edf2f7 !important;
            border-radius: 18px !important;
            background: linear-gradient(180deg, #ffffff 0%, #fafcff 100%) !important;
            padding: 16px !important;
            margin-bottom: 16px !important;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.04) !important;
        }

        .tattoo-inbox-page .ti-detail-section:last-child {
            margin-bottom: 0 !important;
        }

        .tattoo-inbox-page .ti-detail-title {
            font-size: 12px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: .08em !important;
            color: #6b7280 !important;
            margin-bottom: 14px !important;
        }

        .tattoo-inbox-page .ti-kv {
            margin-bottom: 12px !important;
        }

        .tattoo-inbox-page .ti-kv:last-child {
            margin-bottom: 0 !important;
        }

        .tattoo-inbox-page .ti-kv-label {
            font-size: 11px !important;
            color: #6b7280 !important;
            margin-bottom: 2px !important;
            text-transform: uppercase !important;
            letter-spacing: .04em !important;
            font-weight: 700 !important;
        }

        .tattoo-inbox-page .ti-kv-value {
            font-size: 13px !important;
            color: #111827 !important;
            font-weight: 700 !important;
            line-height: 1.4 !important;
            word-break: break-word !important;
        }

        .tattoo-inbox-page .ti-quote-card {
            border: 1px solid #e8edf5 !important;
            border-radius: 16px !important;
            background: #fff !important;
            padding: 15px !important;
            margin-bottom: 14px !important;
            box-shadow: 0 10px 22px rgba(15, 23, 42, 0.04) !important;
        }

        .tattoo-inbox-page .ti-quote-card:last-child {
            margin-bottom: 0 !important;
        }

        .tattoo-inbox-page .ti-loading {
            padding: 20px !important;
            text-align: center !important;
            color: #6b7280 !important;
            font-weight: 600 !important;
        }

        .tattoo-inbox-page .ti-mini-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            border: 1px solid #111827 !important;
            background: #fff !important;
            color: #111827 !important;
            border-radius: 10px !important;
            padding: 8px 12px !important;
            font-size: 12px !important;
            font-weight: 700 !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .tattoo-inbox-page .ti-mini-btn:hover {
            background: #111827 !important;
            color: #fff !important;
        }

        @media (max-width: 1200px) {

            .tattoo-inbox-page .ti-col-3,
            .tattoo-inbox-page .ti-col-left,
            .tattoo-inbox-page .ti-col-center,
            .tattoo-inbox-page .ti-col-right {
                width: 100% !important;
            }

            .tattoo-inbox-page .ti-conversation-list,
            .tattoo-inbox-page .ti-chat-body,
            .tattoo-inbox-page .ti-side-body {
                max-height: none !important;
            }

            .tattoo-inbox-page .ti-chat-shell,
            .tattoo-inbox-page .ti-panel {
                min-height: auto !important;
            }
        }

        @media (max-width: 767px) {
            .tattoo-inbox-page {
                padding: 14px !important;
            }

            .tattoo-inbox-page .ti-topbar,
            .tattoo-inbox-page .ti-summary-wrap,
            .tattoo-inbox-page .ti-content-wrap {
                padding: 16px !important;
            }

            .tattoo-inbox-page .ti-main-title {
                font-size: 26px !important;
            }

            .tattoo-inbox-page .ti-brand-icon {
                width: 46px !important;
                height: 46px !important;
                border-radius: 14px !important;
            }

            .tattoo-inbox-page .ti-title-wrap {
                align-items: flex-start !important;
            }

            .tattoo-inbox-page .ti-conversation-top {
                flex-direction: column !important;
                align-items: flex-start !important;
            }

            .tattoo-inbox-page .ti-conversation-date {
                text-align: left !important;
            }

            .tattoo-inbox-page .ti-chat-form-row {
                flex-direction: column !important;
            }

            .tattoo-inbox-page .ti-textarea {
                border-radius: 14px !important;
                border-right: 1px solid #dbe3ef !important;
                margin-bottom: 10px !important;
            }

            .tattoo-inbox-page .ti-send-btn {
                border-radius: 14px !important;
                width: 100% !important;
            }
        }
    </style>
@endpush

@section('content')
    <div class="tattoo-inbox-page">
        <div class="ti-shell">
            <div class="ti-topbar">
                <div class="ti-title-wrap">
                    <div class="ti-brand-icons">
                        <span class="ti-brand-icon whatsapp">
                            <i class="fa fa-whatsapp"></i>
                        </span>
                        <span class="ti-brand-icon instagram">
                            <i class="fa fa-instagram"></i>
                        </span>
                    </div>

                    <div>
                        <h2 class="ti-main-title">Tattoo Inbox</h2>
                        <p class="ti-main-subtitle">
                            Gestión visual de conversaciones, leads y cotizaciones desde WhatsApp e Instagram
                        </p>
                    </div>
                </div>

                <button type="button" class="ti-refresh-btn" id="btnRefreshInbox">
                    <i class="fa fa-refresh mr-1"></i> Refrescar bandeja
                </button>
            </div>

            <div class="ti-summary-wrap">
                <div class="ti-summary-row">
                    <div class="ti-col ti-col-3">
                        <div class="ti-summary-card total ti-summary-clickable" data-summary-filter="all">
                            <div class="ti-summary-label">Conversaciones</div>
                            <div class="ti-summary-value" id="summaryConversationCount">{{ $conversationCount ?? 0 }}</div>
                            <div class="ti-summary-help">Inbox total entre ambos canales</div>
                        </div>
                    </div>

                    <div class="ti-col ti-col-3">
                        <div class="ti-summary-card leads ti-summary-clickable" data-summary-filter="leads">
                            <div class="ti-summary-label">Leads</div>
                            <div class="ti-summary-value" id="summaryLeadCount">{{ $leadCount ?? 0 }}</div>
                            <div class="ti-summary-help">Clientes potenciales activos</div>
                        </div>
                    </div>

                    <div class="ti-col ti-col-3">
                        <div class="ti-summary-card open ti-summary-clickable" data-summary-filter="open">
                            <div class="ti-summary-label">Abiertas</div>
                            <div class="ti-summary-value" id="summaryOpenCount">{{ $openCount ?? 0 }}</div>
                            <div class="ti-summary-help">Conversaciones en seguimiento</div>
                        </div>
                    </div>

                    <div class="ti-col ti-col-3">
                        <div class="ti-summary-card quoted ti-summary-clickable" data-summary-filter="quoted">
                            <div class="ti-summary-label">Cotizadas</div>
                            <div class="ti-summary-value" id="summaryQuotedCount">{{ $quotedCount ?? 0 }}</div>
                            <div class="ti-summary-help">Con rango o precio sugerido</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ti-content-wrap">
                <div class="ti-content-row">
                    <div class="ti-col ti-col-left">
                        <div class="ti-panel">
                            <div class="ti-panel-header">
                                <h3 class="ti-panel-title">Bandeja</h3>
                                <p class="ti-panel-subtitle">Conversaciones recientes con vista comercial</p>
                            </div>

                            <div class="ti-sticky-toolbar">
                                <input type="text" class="ti-input" id="filterSearch"
                                    placeholder="Buscar por nombre, usuario, teléfono o canal">

                                <div class="ti-filter-grid">
                                    <div>
                                        <select class="ti-select" id="filterChannel">
                                            <option value="">Todos</option>
                                            <option value="whatsapp">WhatsApp</option>
                                            <option value="instagram">Instagram</option>
                                        </select>
                                    </div>
                                    <div>
                                        <select class="ti-select" id="filterStatus">
                                            <option value="">Estado</option>
                                            <option value="open">Open</option>
                                            <option value="closed">Closed</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="ti-conversation-list" id="conversationList">
                                <div class="ti-loading">Cargando conversaciones...</div>
                            </div>
                        </div>
                    </div>

                    <div class="ti-col ti-col-center">
                        <div class="ti-panel">
                            <div class="ti-chat-shell">
                                <div class="ti-chat-head" id="chatHeader">
                                    <div>
                                        <h3 class="ti-panel-title">Selecciona una conversación</h3>
                                        <p class="ti-panel-subtitle">Aquí verás el chat completo con estilo de atención</p>
                                    </div>
                                </div>

                                <div class="ti-chat-body" id="chatBody">
                                    <div class="ti-chat-empty">
                                        Selecciona una conversación del panel izquierdo para ver los mensajes.
                                    </div>
                                </div>

                                <div class="ti-chat-footer">
                                    <form id="sendMessageForm" autocomplete="off">
                                        @csrf
                                        <div class="ti-chat-form-row">
                                            <textarea class="ti-textarea" id="replyText" rows="2" placeholder="Escribe una respuesta..." disabled></textarea>
                                            <button class="ti-send-btn" type="submit" id="btnSendReply" disabled>
                                                <i class="fa fa-paper-plane mr-1"></i> Enviar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ti-col ti-col-right">
                        <div class="ti-panel">
                            <div class="ti-panel-header">
                                <h3 class="ti-panel-title">Detalle</h3>
                                <p class="ti-panel-subtitle">Lead, intención y cotización</p>
                            </div>

                            <div class="ti-side-body" id="sidePanelBody">
                                <div class="ti-side-empty">
                                    Selecciona una conversación para ver la información del lead y la cotización.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function(window, document, $) {
            'use strict';

            if (!$) return;

            function initTattooInbox() {
                var $page = $('.tattoo-inbox-page');

                if (!$page.length) return;
                if ($page.data('tattooInboxInitialized') === true) return;

                $page.data('tattooInboxInitialized', true);

                var selectedConversationId = null;
                var summaryFilter = 'all';

                var routes = {
                    list: @json(route('tenant.tattoo-inbox.conversations')),
                    detailBase: @json(url('tattoo-inbox/conversation')),
                    csrf: @json(csrf_token())
                };

                var $conversationList = $('#conversationList');
                var $chatHeader      = $('#chatHeader');
                var $chatBody        = $('#chatBody');
                var $sidePanelBody   = $('#sidePanelBody');
                var $replyText       = $('#replyText');
                var $btnSendReply    = $('#btnSendReply');
                var $filterSearch    = $('#filterSearch');
                var $filterChannel   = $('#filterChannel');
                var $filterStatus    = $('#filterStatus');
                var $btnRefreshInbox = $('#btnRefreshInbox');

                /* ── helpers ── */
                function escapeHtml(text) {
                    if (text === null || text === undefined) return '';
                    return String(text)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');
                }

                function nl2br(text) {
                    return escapeHtml(text).replace(/\n/g, '<br>');
                }

                function numberOrDash(value) {
                    return (value !== null && value !== undefined && value !== '') ? value : '-';
                }

                function channelLabel(channel) {
                    return channel === 'whatsapp' ? 'WhatsApp'
                         : channel === 'instagram' ? 'Instagram'
                         : (channel || '-');
                }

                function channelIcon(channel) {
                    return channel === 'whatsapp' ? 'fa-whatsapp' : 'fa-instagram';
                }

                function quoteBadgeClass(status) {
                    if (status === 'quoted')       return 'quoted';
                    if (status === 'pending_data') return 'pending_data';
                    return 'open';
                }

                function statusBadgeClass(status) {
                    return status === 'closed' ? 'closed' : 'open';
                }

                /* ── summary ── */
                function applySummaryFilter(rows) {
                    if (!rows || !rows.length) return [];
                    if (summaryFilter === 'open')   return rows.filter(function(r) { return (r.status || 'open') === 'open'; });
                    if (summaryFilter === 'quoted')  return rows.filter(function(r) { return (r.quote_status || '') === 'quoted'; });
                    if (summaryFilter === 'leads')   return rows.filter(function(r) { return !!r.lead; });
                    return rows;
                }

                function setActiveSummaryCard() {
                    $('.ti-summary-clickable').removeClass('is-active');
                    $('.ti-summary-clickable[data-summary-filter="' + summaryFilter + '"]').addClass('is-active');
                }

                function recalcSummaries(rows) {
                    var totalConversations = rows.filter(function(r) { return r.row_type === 'conversation'; }).length;
                    var totalLeads  = rows.length;
                    var open        = rows.filter(function(r) { return (r.status || 'open') === 'open'; }).length;
                    var quoted      = rows.filter(function(r) { return (r.quote_status || '') === 'quoted'; }).length;

                    $('#summaryConversationCount').text(totalConversations);
                    $('#summaryLeadCount').text(totalLeads);
                    $('#summaryOpenCount').text(open);
                    $('#summaryQuotedCount').text(quoted);
                }

                /* ── empty state ── */
                function resetEmptyState(title, subtitle, bodyText, sideText) {
                    $chatHeader.html(
                        '<div>' +
                        '<h3 class="ti-panel-title">' + escapeHtml(title) + '</h3>' +
                        '<p class="ti-panel-subtitle">' + escapeHtml(subtitle) + '</p>' +
                        '</div>'
                    );
                    $chatBody.html('<div class="ti-chat-empty">' + escapeHtml(bodyText) + '</div>');
                    $sidePanelBody.html('<div class="ti-side-empty">' + escapeHtml(sideText) + '</div>');
                    $replyText.prop('disabled', true).val('');
                    $btnSendReply.prop('disabled', true);
                }

                /* ── render list ── */
                function renderConversationList(rows) {
                    if (!rows || !rows.length) {
                        $conversationList.html('<div class="ti-loading">No hay conversaciones para mostrar.</div>');
                        recalcSummaries([]);
                        return;
                    }

                    recalcSummaries(rows);

                    var isSingle = rows.length === 1;
                    var html = '';

                    rows.forEach(function(row) {
                        var lead        = row.lead || {};
                        var leadName    = lead.full_name || 'Sin nombre';
                        var leadPhone   = lead.phone || lead.external_user_id || lead.instagram_username || '-';
                        var preview     = row.last_incoming_text || 'Sin mensaje';
                        var isLeadOnly  = row.row_type === 'lead_only';

                        var quoteInfo = isLeadOnly
                            ? 'Lead sin conversación'
                            : (row.latest_quote && (row.latest_quote.price_min || row.latest_quote.price_max)
                                ? ('S/ ' + numberOrDash(row.latest_quote.price_min) + ' - S/ ' + numberOrDash(row.latest_quote.price_max))
                                : 'Sin cotización cerrada');

                        var extraClass  = isSingle ? 'single-mode' : '';
                        var activeClass = String(selectedConversationId) === String(row.id) ? 'active' : '';

                        html += '<button type="button" class="ti-conversation-item ' + extraClass + ' ' + activeClass + '" data-id="' + escapeHtml(row.id) + '">';
                        html += '  <div class="ti-conversation-top">';
                        html += '    <div class="ti-conversation-left">';
                        html += '      <div class="ti-avatar ' + escapeHtml(row.channel || '') + '"><i class="fa ' + channelIcon(row.channel) + '"></i></div>';
                        html += '      <div style="min-width:0; flex:1 1 auto;">';
                        html += '        <div class="ti-conversation-name">' + escapeHtml(leadName) + '</div>';
                        html += '        <div class="ti-conversation-phone">' + escapeHtml(leadPhone) + '</div>';
                        html += '      </div>';
                        html += '    </div>';
                        html += '    <div class="ti-conversation-date">' + escapeHtml(row.last_message_at || '-') + '</div>';
                        html += '  </div>';

                        html += '  <div class="ti-chip-row">';
                        html += '    <span class="ti-badge ' + escapeHtml(row.channel || '') + '"><i class="fa ' + channelIcon(row.channel) + '"></i> ' + escapeHtml(channelLabel(row.channel)) + '</span>';
                        if (isLeadOnly) {
                            html += '    <span class="ti-badge pending_data">lead nuevo</span>';
                        } else {
                            html += '    <span class="ti-badge ' + quoteBadgeClass(row.quote_status) + '">' + escapeHtml(row.quote_status || '-') + '</span>';
                            html += '    <span class="ti-badge ' + statusBadgeClass(row.status) + '">' + escapeHtml(row.status || '-') + '</span>';
                        }
                        html += '  </div>';

                        html += '  <div class="ti-conversation-preview">' + escapeHtml(preview) + '</div>';
                        html += '  <div class="ti-price-highlight">' + escapeHtml(quoteInfo) + '</div>';
                        html += '</button>';
                    });

                    $conversationList.html(html);
                }

                /* ── render chat ── */
                function renderChatHeader(data) {
                    var lead    = data.lead || {};
                    var channel = data.channel || '-';
                    var name    = lead.full_name || 'Sin nombre';
                    var sub     = channelLabel(channel) + ' · ' + (lead.phone || lead.instagram_username || lead.external_user_id || '-');

                    $chatHeader.html(
                        '<div class="ti-chat-head-inner">' +
                        '  <div class="ti-chat-avatar ' + escapeHtml(channel) + '"><i class="fa ' + channelIcon(channel) + '"></i></div>' +
                        '  <div style="min-width:0;">' +
                        '    <h3 class="ti-panel-title" style="margin-bottom:4px !important;">' + escapeHtml(name) + '</h3>' +
                        '    <p class="ti-panel-subtitle" style="margin-bottom:0 !important;">' + escapeHtml(sub) + '</p>' +
                        '  </div>' +
                        '</div>'
                    );
                }

                function renderMessages(messages) {
                    if (!messages || !messages.length) {
                        $chatBody.html('<div class="ti-chat-empty">Este lead aún no tiene conversación iniciada.</div>');
                        return;
                    }

                    var html = '';

                    messages.forEach(function(msg) {
                        var direction  = msg.direction || 'in';
                        var dateText   = msg.sent_at || msg.received_at || msg.created_at || '';
                        var statusText = msg.status || '';
                        var automated  = msg.is_automated ? ' · automático' : '';
                        var text       = msg.message_text || '[Sin texto]';

                        html += '<div class="ti-message-row ' + escapeHtml(direction) + '">';
                        html += '  <div class="ti-message-bubble">';
                        html += nl2br(text);

                        if (msg.media_url) {
                            html += '<div style="margin-top:8px;"><small>Media: ' + escapeHtml(msg.media_url) + '</small></div>';
                        }

                        html += '    <div class="ti-message-meta">' + escapeHtml(dateText + (statusText ? ' · ' + statusText : '') + automated) + '</div>';
                        html += '  </div>';
                        html += '</div>';
                    });

                    $chatBody.html(html);
                    var cb = $chatBody.get(0);
                    if (cb) cb.scrollTop = cb.scrollHeight;
                }

                /* ── render side panel ── */
                function renderSidePanel(data) {
                    var lead   = data.lead || {};
                    var quotes = data.quotes || [];
                    var html   = '';

                    html += '<div class="ti-detail-section">';
                    html += '  <div class="ti-detail-title">Lead</div>';
                    html += '  <div class="ti-kv"><div class="ti-kv-label">Nombre</div><div class="ti-kv-value">'    + escapeHtml(lead.full_name || '-') + '</div></div>';
                    html += '  <div class="ti-kv"><div class="ti-kv-label">Teléfono</div><div class="ti-kv-value">'  + escapeHtml(lead.phone || lead.external_user_id || '-') + '</div></div>';
                    html += '  <div class="ti-kv"><div class="ti-kv-label">Instagram</div><div class="ti-kv-value">' + escapeHtml(lead.instagram_username || '-') + '</div></div>';
                    html += '  <div class="ti-kv"><div class="ti-kv-label">Email</div><div class="ti-kv-value">'     + escapeHtml(lead.email || '-') + '</div></div>';
                    html += '  <div class="ti-kv"><div class="ti-kv-label">Estado</div><div class="ti-kv-value">'    + escapeHtml(lead.status || '-') + '</div></div>';
                    html += '  <div class="ti-kv"><div class="ti-kv-label">Notas</div><div class="ti-kv-value">'     + escapeHtml(lead.notes || '-') + '</div></div>';
                    html += '</div>';

                    html += '<div class="ti-detail-section">';
                    html += '  <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-bottom:12px;">';
                    html += '    <div class="ti-detail-title" style="margin-bottom:0 !important;">Cotización</div>';
                    html += '    <button type="button" class="ti-mini-btn" id="btnRecalculateQuote"><i class="fa fa-calculator"></i> Recalcular</button>';
                    html += '  </div>';

                    if (!quotes.length) {
                        html += '<div class="ti-kv-value" style="color:#6b7280 !important;">Todavía no hay cotización para este lead.</div>';
                    } else {
                        quotes.forEach(function(q) {
                            html += '<div class="ti-quote-card">';
                            html += '  <div class="ti-chip-row" style="margin-bottom:10px !important;"><span class="ti-badge ' + quoteBadgeClass(q.status) + '">' + escapeHtml(q.status || '-') + '</span></div>';
                            html += '  <div class="ti-kv"><div class="ti-kv-label">Zona</div><div class="ti-kv-value">'          + escapeHtml(q.body_zone || '-') + '</div></div>';
                            html += '  <div class="ti-kv"><div class="ti-kv-label">Estilo</div><div class="ti-kv-value">'        + escapeHtml(q.style || '-') + '</div></div>';
                            html += '  <div class="ti-kv"><div class="ti-kv-label">Tamaño</div><div class="ti-kv-value">'        + escapeHtml(q.size_text || (q.size_cm ? q.size_cm + ' cm' : '-')) + '</div></div>';
                            html += '  <div class="ti-kv"><div class="ti-kv-label">Color</div><div class="ti-kv-value">'         + (q.is_color ? 'Sí' : 'No') + '</div></div>';
                            html += '  <div class="ti-kv"><div class="ti-kv-label">Cover-up</div><div class="ti-kv-value">'      + (q.is_coverup ? 'Sí' : 'No') + '</div></div>';
                            html += '  <div class="ti-kv"><div class="ti-kv-label">Detalle</div><div class="ti-kv-value">'       + escapeHtml(q.detail_level || '-') + '</div></div>';
                            html += '  <div class="ti-kv"><div class="ti-kv-label">Referencia</div><div class="ti-kv-value">'    + (q.has_reference ? 'Sí (' + escapeHtml(q.reference_count || 0) + ')' : 'No') + '</div></div>';
                            html += '  <div class="ti-kv"><div class="ti-kv-label">Rango</div><div class="ti-kv-value">S/ '      + escapeHtml(numberOrDash(q.price_min)) + ' - S/ ' + escapeHtml(numberOrDash(q.price_max)) + '</div></div>';
                            html += '  <div class="ti-kv"><div class="ti-kv-label">Sugerido</div><div class="ti-kv-value">S/ '   + escapeHtml(numberOrDash(q.suggested_price)) + '</div></div>';
                            html += '  <div class="ti-kv"><div class="ti-kv-label">Idea del cliente</div><div class="ti-kv-value">' + escapeHtml(q.customer_idea || '-') + '</div></div>';
                            html += '  <div class="ti-kv"><div class="ti-kv-label">Cotizada</div><div class="ti-kv-value">'      + escapeHtml(q.quoted_at || '-') + '</div></div>';
                            html += '</div>';
                        });
                    }

                    html += '</div>';
                    $sidePanelBody.html(html);
                }

                /* ── load conversations ── */
                function loadConversations(autoSelectFirst) {
                    $conversationList.html('<div class="ti-loading">Cargando conversaciones...</div>');

                    $.ajax({
                        url: routes.list,
                        type: 'GET',
                        data: {
                            search:  $filterSearch.val(),
                            channel: $filterChannel.val(),
                            status:  $filterStatus.val()
                        }
                    }).done(function(response) {
                        if (response && response.success) {
                            var rows = [];
                            if (Array.isArray(response.data)) {
                                rows = response.data;
                            } else if (response.data && Array.isArray(response.data.rows)) {
                                rows = response.data.rows;
                            }

                            var filteredRows = applySummaryFilter(rows);
                            renderConversationList(filteredRows);
                            setActiveSummaryCard();

                            if (autoSelectFirst && filteredRows.length) {
                                selectedConversationId = filteredRows[0].id;
                                renderConversationList(filteredRows);
                                loadConversationDetail(selectedConversationId);
                            } else if (!filteredRows.length) {
                                selectedConversationId = null;
                                resetEmptyState(
                                    'Sin resultados',
                                    'No hay elementos para ese filtro.',
                                    'No hay conversaciones o leads para ese filtro.',
                                    'No hay detalle disponible.'
                                );
                            }
                        } else {
                            renderConversationList([]);
                            setActiveSummaryCard();
                        }
                    }).fail(function(xhr) {
                        console.error('Error cargando conversaciones:', xhr);
                        renderConversationList([]);
                        resetEmptyState(
                            'Error al cargar',
                            'No se pudo obtener la bandeja.',
                            'No se pudieron cargar los mensajes.',
                            'No se pudo cargar el detalle.'
                        );
                    });
                }

                /* ── load detail ── */
                function loadConversationDetail(id) {
                    if (!id) return;

                    $chatBody.html('<div class="ti-loading">Cargando conversación...</div>');
                    $sidePanelBody.html('<div class="ti-loading">Cargando detalle...</div>');
                    $replyText.prop('disabled', false);
                    $btnSendReply.prop('disabled', false);

                    $.ajax({
                        url: routes.detailBase + '/' + id,
                        type: 'GET'
                    }).done(function(response) {
                        if (!response || !response.success) {
                            $chatBody.html('<div class="ti-chat-empty">No se pudo cargar la conversación.</div>');
                            $sidePanelBody.html('<div class="ti-side-empty">No se pudo cargar el detalle.</div>');
                            return;
                        }
                        var data = response.data || {};
                        renderChatHeader(data);
                        renderMessages(data.messages || []);
                        renderSidePanel(data);
                    }).fail(function(xhr) {
                        console.error('Error cargando detalle:', xhr);
                        $chatBody.html('<div class="ti-chat-empty">Error cargando conversación.</div>');
                        $sidePanelBody.html('<div class="ti-side-empty">Error cargando detalle.</div>');
                    });
                }

                /* ── events ── */
                $(document).on('click', '.ti-conversation-item', function() {
                    selectedConversationId = $(this).data('id');
                    $('.ti-conversation-item').removeClass('active');
                    $(this).addClass('active');
                    loadConversationDetail(selectedConversationId);
                });

                $(document).on('click', '.ti-summary-clickable', function() {
                    summaryFilter = $(this).data('summary-filter') || 'all';
                    selectedConversationId = null;
                    setActiveSummaryCard();
                    loadConversations(true);
                });

                $btnRefreshInbox.on('click', function() {
                    selectedConversationId = null;
                    loadConversations(true);
                });

                $filterSearch.on('keyup', function(e) {
                    if (e.keyCode === 13) {
                        selectedConversationId = null;
                        loadConversations(true);
                    }
                });

                $filterChannel.add($filterStatus).on('change', function() {
                    selectedConversationId = null;
                    loadConversations(true);
                });

                /* ── send message ── */
                $('#sendMessageForm').on('submit', function(e) {
                    e.preventDefault();

                    if (!selectedConversationId) { alert('Selecciona una conversación primero.'); return; }

                    var text = ($replyText.val() || '').trim();
                    if (!text) { alert('Escribe un mensaje.'); return; }

                    $btnSendReply.prop('disabled', true).html('<i class="fa fa-spinner fa-spin mr-1"></i> Enviando...');

                    $.ajax({
                        url: routes.detailBase + '/' + selectedConversationId + '/send',
                        type: 'POST',
                        data: { _token: routes.csrf, text: text }
                    }).done(function(response) {
                        if (response && response.success) {
                            $replyText.val('');
                            loadConversationDetail(selectedConversationId);
                            loadConversations(false);
                        } else {
                            alert((response && response.message) ? response.message : 'No se pudo enviar el mensaje.');
                        }
                    }).fail(function(xhr) {
                        alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo enviar el mensaje.');
                    }).always(function() {
                        $btnSendReply.prop('disabled', false).html('<i class="fa fa-paper-plane mr-1"></i> Enviar');
                    });
                });

                /* ── recalculate quote ── */
                $(document).on('click', '#btnRecalculateQuote', function() {
                    if (!selectedConversationId) { alert('Selecciona una conversación primero.'); return; }

                    var $btn = $(this);
                    $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Calculando...');

                    $.ajax({
                        url: routes.detailBase + '/' + selectedConversationId + '/recalculate-quote',
                        type: 'POST',
                        data: { _token: routes.csrf }
                    }).done(function(response) {
                        if (response && response.success) {
                            loadConversationDetail(selectedConversationId);
                            loadConversations(false);
                        } else {
                            alert((response && response.message) ? response.message : 'No se pudo recalcular la cotización.');
                        }
                    }).fail(function(xhr) {
                        alert((xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'No se pudo recalcular la cotización.');
                    }).always(function() {
                        $btn.prop('disabled', false).html('<i class="fa fa-calculator"></i> Recalcular');
                    });
                });

                /* ── init ── */
                setActiveSummaryCard();
                loadConversations(true);
            }

            $(document).ready(initTattooInbox);
        })(window, document, window.jQuery);
    </script>
@endpush