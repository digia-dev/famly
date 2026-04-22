<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Rename families to groups if families exists
        if (Schema::hasTable('families') && !Schema::hasTable('groups')) {
            Schema::rename('families', 'groups');
        }

        // 2. Add/Rename columns to groups
        Schema::table('groups', function (Blueprint $table) {
            if (Schema::hasColumn('groups', 'family_name') && !Schema::hasColumn('groups', 'name')) {
                $table->renameColumn('family_name', 'name');
            }
            if (!Schema::hasColumn('groups', 'type')) {
                $table->string('type')->default('Family')->after('id');
            }
            if (!Schema::hasColumn('groups', 'invite_code')) {
                $table->string('invite_code')->nullable()->unique()->after('type');
            }
            if (!Schema::hasColumn('groups', 'admin_id')) {
                $table->unsignedBigInteger('admin_id')->nullable()->after('invite_code');
            }
        });

        // 3. Create group_members pivot table
        if (!Schema::hasTable('group_members')) {
            Schema::create('group_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('role')->default('member'); // admin, member
                $table->string('status')->default('Active'); // Pending, Active, Blocked
                $table->timestamps();
            });
        }

        // 4. Update foreign keys in existing tables (Rename family_id to group_id)
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'family_id') && !Schema::hasColumn('users', 'group_id')) {
                $table->renameColumn('family_id', 'group_id');
            }
            if (!Schema::hasColumn('users', 'current_group_id')) {
                $table->unsignedBigInteger('current_group_id')->nullable()->after('id');
            }
        });

        Schema::table('kategori_nama_tabungans', function (Blueprint $table) {
            if (Schema::hasColumn('kategori_nama_tabungans', 'family_id') && !Schema::hasColumn('kategori_nama_tabungans', 'group_id')) {
                $table->renameColumn('family_id', 'group_id');
            }
            if (!Schema::hasColumn('kategori_nama_tabungans', 'is_group')) {
                // If is_personal exists, we'll reconcile later, for now ensure is_group exists
                $table->boolean('is_group')->default(true)->after('id');
            }
            if (!Schema::hasColumn('kategori_nama_tabungans', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('is_group');
            }
        });

        Schema::table('tabungans', function (Blueprint $table) {
            if (Schema::hasColumn('tabungans', 'family_id') && !Schema::hasColumn('tabungans', 'group_id')) {
                $table->renameColumn('family_id', 'group_id');
            }
        });

        Schema::table('planned_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('planned_transactions', 'family_id') && !Schema::hasColumn('planned_transactions', 'group_id')) {
                $table->renameColumn('family_id', 'group_id');
            }
        });
        
        // 5. Create Chat Messages table
        if (!Schema::hasTable('chat_messages')) {
            Schema::create('chat_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->text('message');
                $table->string('type')->default('text'); // text, ai_insight, system
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('group_members');

        // Note: Full reversal might be destructive if columns already existed from other migrations.
        // We typically don't rollback complex refactoring migrations in dev unless necessary.
    }
};
