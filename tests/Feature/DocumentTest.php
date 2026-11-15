<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Documents joints : idées de projets en PDF, Word, présentations, logs…
 */
class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
        Storage::fake('local');
        Storage::fake('public');
    }

    private function fixture(string $name, ?string $as = null): UploadedFile
    {
        return new UploadedFile(base_path('tests/fixtures/'.$name), $as ?? $name, null, null, true);
    }

    private function postData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Idée de projet : paiement Mobile Money',
            'content' => 'Je partage le cahier des charges de mon idée de projet, vos avis sont bienvenus.',
            'type' => 'resource',
            'category_id' => Category::first()->id,
        ], $overrides);
    }

    public function test_on_peut_joindre_pdf_word_tableur_markdown_et_log(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/posts', $this->postData([
            'documents' => [$this->fixture('idee-projet.pdf'), $this->fixture('idee-projet.docx'), $this->fixture('budget.xlsx')],
        ]))->assertSessionHasNoErrors();

        $post = Post::first();
        $docs = $post->attachments()->where('kind', 'document')->get();
        $this->assertCount(3, $docs);
        foreach ($docs as $doc) {
            Storage::disk('local')->assertExists($doc->path);
            Storage::disk('public')->assertMissing($doc->path); // jamais dans le dossier public
        }
        $this->assertSame(['idee-projet.pdf', 'idee-projet.docx', 'budget.xlsx'], $docs->pluck('original_name')->all());

        $this->actingAs($user)->post('/posts', $this->postData([
            'title' => 'Mes notes et mon journal d\'erreurs',
            'documents' => [$this->fixture('notes.md'), $this->fixture('erreur.log')],
        ]))->assertSessionHasNoErrors();
    }

    public function test_les_fichiers_dangereux_sont_refuses(): void
    {
        $user = User::factory()->create();

        foreach ([
            $this->fixture('piege.txt'),                 // PHP déguisé en .txt
            $this->fixture('page.html'),                 // page web
            $this->fixture('idee-projet.pdf', 'facture.php'), // bonne nature, extension interdite
            $this->fixture('page.html', 'page.pdf'),     // HTML déguisé en PDF
            UploadedFile::fake()->create('setup.exe', 20, 'application/x-msdownload'),
            UploadedFile::fake()->create('archive.zip', 20, 'application/zip'),
        ] as $file) {
            $this->actingAs($user)->post('/posts', $this->postData(['documents' => [$file]]))
                ->assertSessionHasErrors('documents.0');
        }

        $this->assertSame(0, Post::count());
        $this->assertSame(0, Attachment::count());
    }

    public function test_limites_trois_documents_et_cinq_mo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/posts', $this->postData([
            'documents' => array_map(fn () => $this->fixture('idee-projet.pdf'), range(1, 4)),
        ]))->assertSessionHasErrors('documents');

        $this->actingAs($user)->post('/posts', $this->postData([
            'documents' => [UploadedFile::fake()->create('enorme.pdf', 6000, 'application/pdf')],
        ]))->assertSessionHasErrors('documents.0');
    }

    public function test_le_telechargement_est_reserve_aux_membres_et_securise(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/posts', $this->postData(['documents' => [$this->fixture('idee-projet.docx', 'Mon idée "géniale".docx')]]));
        $doc = Attachment::first();

        auth()->logout();
        $this->get(route('attachments.download', $doc))->assertRedirect('/login');

        $response = $this->actingAs(User::factory()->create())->get(route('attachments.download', $doc));
        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Content-Type', 'application/octet-stream');
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));
        $this->assertStringNotContainsString('"géniale"', $response->headers->get('Content-Disposition'));
    }

    public function test_seuls_les_pdf_s_ouvrent_dans_le_navigateur(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/posts', $this->postData([
            'documents' => [$this->fixture('idee-projet.pdf'), $this->fixture('idee-projet.docx')],
        ]));
        [$pdf, $word] = Attachment::orderBy('id')->get()->all();

        $view = $this->actingAs($user)->get(route('attachments.view', $pdf));
        $view->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('inline;', $view->headers->get('Content-Disposition'));
        $this->assertStringContainsString('sandbox', $view->headers->get('Content-Security-Policy'));

        $this->actingAs($user)->get(route('attachments.view', $word))->assertNotFound();
    }

    public function test_une_image_ne_passe_pas_par_le_telechargement_de_documents(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/posts', $this->postData(['images' => [UploadedFile::fake()->image('a.png')]]));

        $this->actingAs($user)->get(route('attachments.download', Attachment::first()))->assertNotFound();
    }

    public function test_les_noms_de_fichiers_sont_nettoyes(): void
    {
        $this->assertSame('passwd', Attachment::cleanName('../../etc/passwd'));
        $this->assertSame('rapport_final_.pdf', Attachment::cleanName("rapport\"final\x00.pdf"));
        $this->assertSame('document', Attachment::cleanName('...'));
    }

    public function test_une_reponse_peut_contenir_un_document(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('comments.store', $post), [
            'content' => 'Voici mon journal d\'erreurs complet.',
            'documents' => [$this->fixture('erreur.log'), $this->fixture('notes.md')],
        ])->assertSessionHasErrors('documents');

        $this->actingAs($user)->post(route('comments.store', $post), [
            'content' => 'Voici mon journal d\'erreurs complet.',
            'documents' => [$this->fixture('erreur.log')],
        ])->assertSessionHasNoErrors();

        $this->actingAs($user)->get(route('posts.show', $post))
            ->assertInertia(fn (Assert $page) => $page
                ->where('comments.0.attachments.0.kind', 'document')
                ->where('comments.0.attachments.0.original_name', 'erreur.log')
                ->where('comments.0.attachments.0.extension', 'log'));
    }

    public function test_retirer_et_supprimer_efface_les_documents(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/posts', $this->postData([
            'documents' => [$this->fixture('idee-projet.pdf'), $this->fixture('budget.xlsx')],
        ]));
        $post = Post::first();
        [$pdf, $xlsx] = $post->attachments->all();

        $this->actingAs($user)->put(route('posts.update', $post), $this->postData(['remove_images' => [$pdf->id]]))
            ->assertSessionHasNoErrors();
        Storage::disk('local')->assertMissing($pdf->path);
        Storage::disk('local')->assertExists($xlsx->path);

        $this->actingAs($user)->delete(route('posts.destroy', $post));
        Storage::disk('local')->assertMissing($xlsx->path);
        $this->assertSame(0, Attachment::count());
    }

    public function test_maximum_de_documents_en_modification(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/posts', $this->postData([
            'documents' => [$this->fixture('idee-projet.pdf'), $this->fixture('budget.xlsx'), $this->fixture('notes.md')],
        ]));
        $post = Post::first();

        $this->actingAs($user)->put(route('posts.update', $post), $this->postData([
            'documents' => [$this->fixture('erreur.log')],
        ]))->assertSessionHasErrors('documents');

        // Les captures et les documents ont des limites séparées.
        $this->actingAs($user)->put(route('posts.update', $post), $this->postData([
            'images' => [UploadedFile::fake()->image('a.png')],
        ]))->assertSessionHasNoErrors();
    }
}
