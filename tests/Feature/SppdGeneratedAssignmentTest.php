<?php

namespace Tests\Feature;

use App\Models\Sppd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SppdGeneratedAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_sppd_page_shows_generated_assignment_button(): void
    {
        $response = $this->withSession([
            'user' => [
                'username' => 'admin',
                'role' => 'admin',
                'name' => 'Administrator',
            ],
        ])->get('/sppd');

        $response->assertOk();
        $response->assertSee('+ Buat SPPD');
        $response->assertSee('Buat Surat Penugasan');
    }

    public function test_generated_assignment_can_be_saved_with_pdf_and_docx_outputs(): void
    {
        Storage::fake('local');

        $response = $this->withSession([
            'user' => [
                'username' => 'admin',
                'role' => 'admin',
                'name' => 'Administrator',
            ],
        ])->postJson('/sppd/store-generated', [
            'document_number' => '072/MAN2/04/2026',
            'document_date' => '2026-04-08',
            'reference_from' => 'Kankemenag Surakarta',
            'reference_number' => '123/UND/2026',
            'reference_date' => '2026-04-07',
            'reference_subject' => 'Undangan konsultasi',
            'assignees' => [
                [
                    'name' => 'Febriana Kusanindya Budhara, SE',
                    'nip' => '198502122005012001',
                    'rank' => 'Penata Tk.I, III/d',
                    'position' => 'Kepala Urusan Tata Usaha',
                ],
                [
                    'name' => 'Musriati Dewi Utami, S.Pd',
                    'nip' => '197808132014112002',
                    'rank' => 'Penata Muda Tk.I, III/b',
                    'position' => 'Bendahara DIPA',
                ],
            ],
            'assignment_agenda' => 'Konsultasi rekonstruksi pembangunan cagar budaya MAN 2 Surakarta',
            'activity_date' => '2026-04-09',
            'activity_time' => '09:30',
            'activity_location' => 'Kantor Dinas PUPR Surakarta, Jl. Blimbing No 10, Kerten, Laweyan, Surakarta',
            'duration_days' => 1,
            'city' => 'Surakarta',
            'signer_title' => 'Plt. Kepala',
            'signer_name' => 'Sita Kurniasari',
            'signer_nip' => '197901012005012001',
            'status' => 'draft',
            'notes' => 'Arsip dibuat otomatis dari form surat penugasan.',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'record_type' => 'Surat Penugasan',
                'entry_type' => 'generated_assignment',
                'document_number' => '072/MAN2/04/2026',
            ],
        ]);

        $record = Sppd::first();

        $this->assertNotNull($record);
        $this->assertSame('generated_assignment', $record->entry_type);
        $this->assertSame('072/MAN2/04/2026', $record->document_number);
        $this->assertNotNull($record->generated_docx_path);
        $this->assertNotNull($record->local_file_path);

        Storage::disk('local')->assertExists($record->generated_docx_path);
        Storage::disk('local')->assertExists($record->local_file_path);
    }

    public function test_reference_basis_is_omitted_when_optional_fields_are_empty(): void
    {
        Storage::fake('local');

        $response = $this->withSession([
            'user' => [
                'username' => 'admin',
                'role' => 'admin',
                'name' => 'Administrator',
            ],
        ])->postJson('/sppd/store-generated', [
            'document_number' => '072/MAN2/05/2026',
            'document_date' => '2026-04-08',
            'reference_from' => '',
            'reference_number' => '',
            'reference_date' => '',
            'reference_subject' => '',
            'assignees' => [
                [
                    'name' => 'Febriana Kusanindya Budhara, SE',
                    'nip' => '198502122005012001',
                    'rank' => 'Penata Tk.I, III/d',
                    'position' => 'Kepala Urusan Tata Usaha',
                ],
            ],
            'assignment_agenda' => 'Koordinasi internal',
            'activity_date' => '2026-04-09',
            'activity_time' => '09:30',
            'activity_location' => 'MAN 2 Surakarta',
            'duration_days' => 1,
            'city' => 'Surakarta',
            'signer_title' => 'Plt. Kepala',
            'signer_name' => 'Sita Kurniasari',
            'signer_nip' => '',
            'status' => 'draft',
            'notes' => '',
        ]);

        $response->assertOk();

        $record = Sppd::latest('id')->first();

        $this->assertNotNull($record);
        $this->assertFalse((bool) data_get($record->assignment_payload, 'has_reference_basis'));
        $this->assertCount(1, data_get($record->assignment_payload, 'basis_items', []));
    }
}
