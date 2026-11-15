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
 * Captures d'écran jointes aux publications et aux réponses.
 */
class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
        Storage::fake('public');
    }

    private function postData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Erreur 500 après le déploiement',
            'content' => "Voici l'erreur affichée après la mise en ligne de mon application.",
            'type' => 'question',
            'category_id' => Category::first()->id,
        ], $overrides);
    }

    private function image(string $name = 'capture.png'): UploadedFile
    {
        return UploadedFile::fake()->image($name, 800, 600);
    }

    public function test_une_publication_peut_contenir_des_captures(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/posts', $this->postData([
            'images' => [$this->image(), $this->image('erreur.jpg')],
        ]))->assertSessionHasNoErrors();

        $post = Post::first();
        $this->assertCount(2, $post->attachments);
        foreach ($post->attachments as $a) {
            Storage::disk('public')->assertExists($a->path);
            $this->assertSame(800, $a->width);
            $this->assertStringStartsWith('attachments/', $a->path);
        }

        $this->actingAs($user)->get(route('posts.show', $post))
            ->assertInertia(fn (Assert $page) => $page->has('post.attachments', 2)->has('post.attachments.0.url'));
    }

    public function test_le_nom_du_fichier_envoye_n_est_jamais_reutilise(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/posts', $this->postData([
            'images' => [UploadedFile::fake()->image('../../evil.php.png', 100, 100)],
        ]))->assertSessionHasNoErrors();

        $path = Attachment::first()->path;
        $this->assertStringNotContainsString('evil', $path);
        $this->assertStringEndsWith('.png', $path);
    }

    public function test_seules_les_images_sont_acceptees(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/posts', $this->postData([
            'images' => [UploadedFile::fake()->create('script.php', 10, 'application/x-php')],
        ]))->assertSessionHasErrors('images.0');

        $this->actingAs($user)->post('/posts', $this->postData([
            'images' => [UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')],
        ]))->assertSessionHasErrors('images.0');

        $this->assertSame(0, Post::count());
        $this->assertSame(0, Attachment::count());
    }

    public function test_quatre_captures_maximum_et_cinq_mo_par_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/posts', $this->postData([
            'images' => array_map(fn () => $this->image(), range(1, 5)),
        ]))->assertSessionHasErrors('images');

        $this->actingAs($user)->post('/posts', $this->postData([
            'images' => [UploadedFile::fake()->image('enorme.png')->size(6000)],
        ]))->assertSessionHasErrors('images.0');
    }

    public function test_l_auteur_peut_retirer_et_ajouter_des_captures(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/posts', $this->postData(['images' => [$this->image(), $this->image()]]));
        $post = Post::first();
        [$first, $second] = $post->attachments->all();

        $this->actingAs($user)->put(route('posts.update', $post), $this->postData([
            'remove_images' => [$first->id],
            'images' => [$this->image('nouvelle.png')],
        ]))->assertSessionHasNoErrors();

        $this->assertModelMissing($first);
        Storage::disk('public')->assertMissing($first->path);
        $this->assertSame(2, $post->attachments()->count());
        $this->assertTrue($post->attachments()->whereKey($second->id)->exists());
    }

    public function test_on_ne_peut_pas_depasser_quatre_captures_en_modification(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/posts', $this->postData(['images' => [$this->image(), $this->image(), $this->image()]]));
        $post = Post::first();

        $this->actingAs($user)->put(route('posts.update', $post), $this->postData([
            'images' => [$this->image(), $this->image()],
        ]))->assertSessionHasErrors('images');

        $this->assertSame(3, $post->attachments()->count());
    }

    public function test_on_ne_peut_pas_retirer_les_captures_d_une_autre_publication(): void
    {
        $owner = User::factory()->create();
        $this->actingAs($owner)->post('/posts', $this->postData(['images' => [$this->image()]]));
        $victim = Attachment::first();

        $attacker = User::factory()->create();
        $this->actingAs($attacker)->post('/posts', $this->postData(['title' => 'Ma propre publication à moi']));
        $mine = Post::where('user_id', $attacker->id)->first();

        $this->actingAs($attacker)->put(route('posts.update', $mine), $this->postData([
            'remove_images' => [$victim->id],
        ]))->assertSessionHasNoErrors();

        $this->assertModelExists($victim);
        Storage::disk('public')->assertExists($victim->path);

        // Et il ne peut évidemment pas modifier la publication d'autrui.
        $this->actingAs($attacker)->put(route('posts.update', $victim->post_id), $this->postData([
            'remove_images' => [$victim->id],
        ]))->assertForbidden();
        $this->assertModelExists($victim);
    }

    public function test_une_reponse_peut_contenir_deux_captures_maximum(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('comments.store', $post), [
            'content' => 'Voici ce que j\'obtiens de mon côté.',
            'images' => [$this->image(), $this->image(), $this->image()],
        ])->assertSessionHasErrors('images');

        $this->actingAs($user)->post(route('comments.store', $post), [
            'content' => 'Voici ce que j\'obtiens de mon côté.',
            'images' => [$this->image()],
        ])->assertSessionHasNoErrors();

        $comment = Comment::first();
        $this->assertCount(1, $comment->attachments);

        $this->actingAs($user)->get(route('posts.show', $post))
            ->assertInertia(fn (Assert $page) => $page->has('comments.0.attachments', 1));
    }

    public function test_supprimer_une_publication_supprime_les_fichiers(): void
    {
        $author = User::factory()->create();
        $this->actingAs($author)->post('/posts', $this->postData(['images' => [$this->image()]]));
        $post = Post::first();

        $other = User::factory()->create();
        $this->actingAs($other)->post(route('comments.store', $post), ['content' => 'Réponse avec capture', 'images' => [$this->image()]]);

        $paths = Attachment::pluck('path');
        $this->assertCount(2, $paths);

        $this->actingAs($author)->delete(route('posts.destroy', $post));

        $this->assertSame(0, Attachment::count());
        foreach ($paths as $p) {
            Storage::disk('public')->assertMissing($p);
        }
    }

    public function test_supprimer_une_reponse_supprime_ses_fichiers(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('comments.store', $post), ['content' => 'Avec une capture', 'images' => [$this->image()]]);
        $path = Attachment::first()->path;

        $this->actingAs($user)->delete(route('comments.destroy', Comment::first()));

        Storage::disk('public')->assertMissing($path);
        $this->assertSame(0, Attachment::count());
    }

    public function test_supprimer_son_compte_supprime_ses_fichiers(): void
    {
        $user = User::factory()->create(['password' => 'password']);
        $this->actingAs($user)->post('/posts', $this->postData(['images' => [$this->image()]]));
        $path = Attachment::first()->path;

        $this->actingAs($user)->delete('/profile', ['password' => 'password'])->assertRedirect('/');

        Storage::disk('public')->assertMissing($path);
        $this->assertSame(0, Attachment::count());
    }

    public function test_le_fil_affiche_les_miniatures(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/posts', $this->postData(['images' => [$this->image()]]));

        $this->actingAs($user)->get('/dashboard')
            ->assertInertia(fn (Assert $page) => $page->has('posts.data.0.attachments', 1));
    }

    public function test_les_blocs_de_code_gardent_leur_langage(): void
    {
        $html = Post::renderMarkdown("```php\necho 'ok';\n```");
        $this->assertStringContainsString('class="language-php"', $html);
    }
}
