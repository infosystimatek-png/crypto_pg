<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 32)->default('merchant')->after('email');
            $table->string('status', 32)->default('active')->after('role');
            $table->index(['role', 'status']);
        });

        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 40)->unique();
            $table->string('name');
            $table->string('status', 32)->default('active')->index();
            $table->string('default_callback_url', 2048)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('merchant_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 32)->default('owner');
            $table->timestamps();
            $table->unique(['merchant_id', 'user_id']);
        });

        Schema::create('api_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('key_prefix', 32)->unique();
            $table->string('secret_hash');
            $table->string('environment', 16)->default('live');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            $table->index(['merchant_id', 'revoked_at']);
        });

        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->string('url', 2048);
            $table->text('secret_encrypted');
            $table->boolean('is_active')->default(true);
            $table->json('subscribed_events')->nullable();
            $table->timestamps();
        });

        Schema::create('blockchain_networks', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->string('chain_id')->nullable();
            $table->boolean('is_testnet')->default(true);
            $table->boolean('is_enabled')->default(true)->index();
            $table->unsignedInteger('confirmation_threshold')->default(19);
            $table->string('adapter', 32)->default('mock');
            $table->string('explorer_url')->nullable();
            $table->string('native_symbol', 16)->nullable();
            $table->timestamps();
        });

        Schema::create('blockchain_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->constrained('blockchain_networks')->cascadeOnDelete();
            $table->string('code', 16);
            $table->string('name');
            $table->string('contract_address')->nullable();
            $table->unsignedTinyInteger('decimals');
            $table->boolean('is_enabled')->default(true)->index();
            $table->timestamps();
            $table->unique(['network_id', 'code']);
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 40)->unique();
            $table->foreignId('merchant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('network_id')->constrained('blockchain_networks')->cascadeOnDelete();
            $table->string('label');
            $table->string('custody_backend', 32)->default('mock');
            $table->string('key_ref')->nullable();
            $table->string('status', 32)->default('active');
            $table->unsignedInteger('next_derivation_index')->default(0);
            $table->timestamps();
            $table->index(['network_id', 'status']);
        });

        Schema::create('payment_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 40)->unique();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('network_id')->constrained('blockchain_networks')->cascadeOnDelete();
            $table->foreignId('merchant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address', 128);
            $table->unsignedInteger('derivation_index');
            $table->string('derivation_path')->nullable();
            $table->string('status', 32)->default('assigned')->index();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->unique(['network_id', 'address']);
            $table->unique(['wallet_id', 'derivation_index']);
        });

        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 40)->unique();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->string('merchant_order_id');
            $table->foreignId('network_id')->constrained('blockchain_networks');
            $table->foreignId('asset_id')->constrained('blockchain_assets');
            $table->foreignId('payment_address_id')->nullable()->constrained('payment_addresses')->nullOnDelete();
            $table->string('amount_minor', 78);
            $table->string('received_amount_minor', 78)->default('0');
            $table->string('status', 32)->index();
            $table->string('qr_payload', 2048)->nullable();
            $table->string('callback_url', 2048)->nullable();
            $table->unsignedInteger('required_confirmations')->default(19);
            $table->unsignedInteger('confirmations')->default(0);
            $table->string('correlation_id', 64)->index();
            $table->json('metadata')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('detected_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('credited_at')->nullable();
            $table->timestamps();
            $table->unique(['merchant_id', 'merchant_order_id']);
            $table->index(['merchant_id', 'status']);
        });

        Schema::table('payment_addresses', function (Blueprint $table) {
            $table->foreignId('payment_request_id')->nullable()->after('merchant_id')->constrained('payment_requests')->nullOnDelete();
            $table->unique('payment_request_id');
        });

        Schema::create('blockchain_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 40)->unique();
            $table->foreignId('network_id')->constrained('blockchain_networks');
            $table->foreignId('asset_id')->nullable()->constrained('blockchain_assets')->nullOnDelete();
            $table->foreignId('payment_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tx_hash');
            $table->unsignedInteger('log_index')->default(0);
            $table->string('from_address');
            $table->string('to_address')->index();
            $table->string('amount_minor', 78);
            $table->unsignedBigInteger('block_number')->nullable()->index();
            $table->unsignedInteger('confirmations')->default(0);
            $table->string('status', 32)->default('detected')->index();
            $table->string('processing_status', 32)->default('pending')->index();
            $table->json('raw_payload')->nullable();
            $table->timestamp('first_seen_at');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->unique(['network_id', 'tx_hash', 'log_index'], 'btx_network_hash_log_unique');
        });

        Schema::table('payment_requests', function (Blueprint $table) {
            $table->foreignId('blockchain_transaction_id')->nullable()->after('payment_address_id')->constrained('blockchain_transactions')->nullOnDelete();
        });

        Schema::create('blockchain_transaction_confirmations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blockchain_transaction_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('confirmations');
            $table->unsignedBigInteger('block_number')->nullable();
            $table->timestamp('observed_at');
            $table->index(['blockchain_transaction_id', 'observed_at'], 'btx_conf_observed_idx');
        });

        Schema::create('blockchain_sync_cursors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('network_id')->unique()->constrained('blockchain_networks')->cascadeOnDelete();
            $table->string('cursor')->nullable();
            $table->timestamp('last_polled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 40)->unique();
            $table->foreignId('merchant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('asset_id')->constrained('blockchain_assets');
            $table->string('type', 64);
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
            $table->unique(['merchant_id', 'asset_id', 'type'], 'ledger_accounts_owner_asset_type_unique');
        });

        Schema::create('ledger_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 40)->unique();
            $table->foreignId('merchant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 64);
            $table->string('status', 32)->default('posted');
            $table->string('description');
            $table->foreignId('payment_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('blockchain_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->string('idempotency_key')->unique();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('created_by', 32)->default('system');
            $table->timestamp('posted_at');
            $table->timestamps();
            $table->index(['merchant_id', 'posted_at']);
        });

        Schema::create('ledger_postings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('ledger_journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('ledger_accounts');
            $table->foreignId('asset_id')->constrained('blockchain_assets');
            $table->string('direction', 8);
            $table->string('amount_minor', 78);
            $table->string('balance_after_minor', 78)->nullable();
            $table->timestamp('created_at');
            $table->index(['account_id', 'created_at']);
        });

        Schema::create('merchant_balance_projections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained('blockchain_assets');
            $table->string('available_minor', 78)->default('0');
            $table->string('pending_minor', 78)->default('0');
            $table->string('reserved_minor', 78)->default('0');
            $table->unsignedInteger('version')->default(0);
            $table->timestamps();
            $table->unique(['merchant_id', 'asset_id']);
        });

        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 40)->unique();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_request_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->json('payload');
            $table->timestamp('occurred_at');
            $table->timestamps();
            $table->index(['merchant_id', 'type']);
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('webhook_endpoint_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('pending')->index();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('next_retry_at')->nullable()->index();
            $table->unsignedInteger('last_response_code')->nullable();
            $table->text('last_response_body')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('dead_lettered_at')->nullable();
            $table->timestamps();
            $table->unique(['webhook_event_id', 'webhook_endpoint_id']);
        });

        Schema::create('reconciliation_runs', function (Blueprint $table) {
            $table->id();
            $table->string('public_id', 40)->unique();
            $table->string('status', 32)->default('running');
            $table->unsignedInteger('matched_count')->default(0);
            $table->unsignedInteger('unmatched_count')->default(0);
            $table->unsignedInteger('exception_count')->default(0);
            $table->json('summary')->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_run_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('severity', 16)->default('warning');
            $table->foreignId('payment_request_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('blockchain_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('merchant_id')->nullable()->constrained()->nullOnDelete();
            $table->json('payload')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['reconciliation_run_id', 'type']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_type', 32)->default('system');
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->json('properties')->nullable();
            $table->string('correlation_id', 64)->nullable()->index();
            $table->timestamp('created_at');
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('request_hash');
            $table->unsignedInteger('response_code')->nullable();
            $table->json('response_body')->nullable();
            $table->foreignId('payment_request_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('locked_at')->nullable();
            $table->timestamps();
            $table->unique(['merchant_id', 'key']);
        });

        Schema::create('system_events', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->json('payload')->nullable();
            $table->string('correlation_id', 64)->nullable()->index();
            $table->timestamp('occurred_at');
            $table->timestamps();
        });

        Schema::create('mock_chain_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('network_code', 32);
            $table->string('asset_code', 16);
            $table->string('tx_hash')->unique();
            $table->unsignedInteger('log_index')->default(0);
            $table->string('from_address');
            $table->string('to_address')->index();
            $table->string('amount_decimal');
            $table->unsignedBigInteger('block_number')->nullable();
            $table->unsignedInteger('confirmations')->default(0);
            $table->boolean('consumed')->default(false)->index();
            $table->json('raw')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_chain_transactions');
        Schema::dropIfExists('system_events');
        Schema::dropIfExists('idempotency_keys');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('reconciliation_items');
        Schema::dropIfExists('reconciliation_runs');
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_events');
        Schema::dropIfExists('merchant_balance_projections');
        Schema::dropIfExists('ledger_postings');
        Schema::dropIfExists('ledger_journal_entries');
        Schema::dropIfExists('ledger_accounts');
        Schema::dropIfExists('blockchain_sync_cursors');
        Schema::dropIfExists('blockchain_transaction_confirmations');
        Schema::table('payment_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('blockchain_transaction_id');
        });
        Schema::dropIfExists('blockchain_transactions');
        Schema::table('payment_addresses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_request_id');
        });
        Schema::dropIfExists('payment_requests');
        Schema::dropIfExists('payment_addresses');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('blockchain_assets');
        Schema::dropIfExists('blockchain_networks');
        Schema::dropIfExists('webhook_endpoints');
        Schema::dropIfExists('api_credentials');
        Schema::dropIfExists('merchant_users');
        Schema::dropIfExists('merchants');
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role', 'status']);
            $table->dropColumn(['role', 'status']);
        });
    }
};
