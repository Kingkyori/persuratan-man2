<?php

namespace Tests\Feature;

use App\Models\SuratKeluar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SuratKeluarGeneratedLetterTest extends TestCase
{
    use RefreshDatabase;

    public function test_surat_keluar_page_shows_generated_letter_flow_and_share_column(): void
    {
        $response = $this->withSession([
            'user' => [
                'username' => 'admin',
                'role' => 'admin',
                'name' => 'Administrator',
            ],
        ])->get('/surat-keluar');

        $response->assertOk();
        $response->assertSee('+ Buat Surat');
        $response->assertSee('Pilih Jenis Surat');
        $response->assertSee('Share Link');
        $response->assertSee('Surat Undangan');
        $response->assertSee('Surat Keterangan');
    }

    public function test_generated_invitation_letter_can_be_saved_with_automatic_number(): void
    {
        Storage::fake('local');

        SuratKeluar::create([
            'destination' => 'Orang Tua/Wali',
            'entry_type' => 'generated_letter',
            'document_type' => 'undangan',
            'letter_date' => '2026-05-01',
            'letter_number' => '001/Ma.11.31.02/HM.01/05/2026',
            'subject' => 'Undangan',
            'status' => 'draft',
            'share_token' => 'existing-token',
        ]);

        $response = $this->withSession([
            'user' => [
                'username' => 'admin',
                'role' => 'admin',
                'name' => 'Administrator',
            ],
        ])->postJson('/surat-keluar/store-generated', [
            'document_type' => 'undangan',
            'document_date' => '2026-05-04',
            'recipient_name' => 'Bapak/Ibu Orang Tua/Wali Siswa Kelas X',
            'recipient_address' => 'Di Tempat',
            'subject' => 'Rapat Wali Murid',
            'city' => 'Surakarta',
            'agenda' => 'Rapat wali murid kelas X',
            'activity_date' => '2026-05-09',
            'activity_time' => '09:00',
            'activity_place' => 'Aula MAN 2 Surakarta',
            'signer_title' => 'Kepala MAN 2 Surakarta',
            'signer_name' => 'Sita Kurniasari',
            'signer_nip' => '197901012005012001',
            'notes' => 'Konsep undangan otomatis.',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'data' => [
                'entry_type' => 'generated_letter',
                'nomor' => '002/Ma.11.31.02/HM.01/05/2026',
                'document_type_label' => 'Surat Undangan',
            ],
        ]);

        $record = SuratKeluar::latest('id')->first();

        $this->assertNotNull($record);
        $this->assertSame('generated_letter', $record->entry_type);
        $this->assertSame('undangan', $record->document_type);
        $this->assertSame('002/Ma.11.31.02/HM.01/05/2026', $record->letter_number);
        $this->assertNotNull($record->local_file_path);
        $this->assertNotNull($record->generated_docx_path);

        Storage::disk('local')->assertExists($record->local_file_path);
        Storage::disk('local')->assertExists($record->generated_docx_path);
    }
}
