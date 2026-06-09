<?php

use App\Models\Workspace;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('submodule');
            $table->json('settings');
            $table->timestamps();

            $table->unique(['workspace_id', 'submodule']);
        });

        $defaults = config('workspace-settings.defaults', []);

        if (empty($defaults)) {
            return;
        }

        Workspace::query()->chunkById(100, function ($workspaces) use ($defaults) {
            foreach ($workspaces as $workspace) {
                foreach ($defaults as $submodule => $settings) {
                    DB::table('workspace_settings')->updateOrInsert(
                        [
                            'workspace_id' => $workspace->id,
                            'submodule' => $submodule,
                        ],
                        [
                            'settings' => json_encode($settings),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_settings');
    }
};
