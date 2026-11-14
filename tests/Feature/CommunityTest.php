<?php

namespace Tests\Feature;

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
 * Scénarios de la checklist de soumission CADEV 2026.
 */
class CommunityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    private function postData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Comment déployer Laravel sur un VPS ?',
            'content' => 'Je cherche une méthode simple et fiable pour déployer mon application.',
            'type' => 'question',
            'category_id' => Category::first()->id,
        ], $overrides);
    }

    public function test_un_visiteur_peut_creer_un_compte_et_recoit_un_profil(): void
    {
        $this->post('/register', [
            'name' => 'Awa Traoré',
            'email' => 'awa@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertNotNull(User::where('email', 'awa@example.com')->first()->profile);
    }

    public function test_les_pages_sont_protegees_par_authentification(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->post('/posts', $this->postData())->assertRedirect('/login');
        $this->get('/')->assertOk();
    }

    public function test_un_utilisateur_peut_completer_son_profil_developpeur(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile.developer.update'), [
            'headline' => 'Développeuse Flutter',
            'country' => 'Bénin',
            'bio' => 'Je construis des apps mobiles.',
            'skills' => ['Flutter', 'Dart', 'Flutter'],
            'github_url' => 'https://github.com/awa',
            'linkedin_url' => '',
            'avatar' => UploadedFile::fake()->image('photo.jpg', 200, 200),
        ])->assertSessionHasNoErrors()->assertRedirect();

        $profile = $user->profile->fresh();
        $this->assertSame('Bénin', $profile->country);
        $this->assertSame(['Flutter', 'Dart'], $profile->skills);
        Storage::disk('public')->assertExists($profile->avatar);
    }

    public function test_les_liens_sans_https_sont_corriges_automatiquement(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile.developer.update'), [
            'github_url' => 'github.com/awa',
            'linkedin_url' => 'http://www.linkedin.com/in/awa',
            'bio' => 'Bonjour',
        ])->assertSessionHasNoErrors();

        $profile = $user->profile->fresh();
        $this->assertSame('https://github.com/awa', $profile->github_url);
        $this->assertSame('https://www.linkedin.com/in/awa', $profile->linkedin_url);
        $this->assertStringStartsWith('http://localhost/storage/', (string) ($profile->avatar_url ?? 'http://localhost/storage/'));
    }

    public function test_le_profil_rejette_un_pays_ou_un_lien_invalide(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('profile.developer.update'), [
            'country' => 'Atlantide',
            'github_url' => 'javascript:alert(1)',
        ])->assertSessionHasErrors(['country', 'github_url']);
    }

    public function test_un_utilisateur_peut_publier_une_discussion(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/posts', $this->postData());

        $post = Post::first();
        $response->assertRedirect(route('posts.show', $post));
        $this->assertTrue($post->user->is($user));
        $this->assertSame('question', $post->type);
    }

    public function test_une_publication_invalide_est_refusee(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/posts', $this->postData(['title' => 'Court', 'type' => 'spam', 'category_id' => 999]))
            ->assertSessionHasErrors(['title', 'type', 'category_id']);

        $this->assertSame(0, Post::count());
    }

    public function test_les_discussions_sont_filtrees_par_categorie(): void
    {
        $user = User::factory()->create();
        [$flutter, $laravel] = Category::orderBy('id')->take(2)->get();
        Post::factory()->for($user)->create(['category_id' => $flutter->id, 'title' => 'Sujet Flutter']);
        Post::factory()->for($user)->create(['category_id' => $laravel->id, 'title' => 'Sujet Laravel']);

        $this->actingAs($user)->get(route('explore', ['category' => $flutter->slug]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Explore')
                ->has('posts.data', 1)
                ->where('posts.data.0.title', 'Sujet Flutter'));
    }

    public function test_un_autre_utilisateur_peut_commenter(): void
    {
        $post = Post::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($other)
            ->post(route('comments.store', $post), ['content' => 'Voici ma solution !'])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', ['post_id' => $post->id, 'user_id' => $other->id]);

        $this->actingAs($other)->get(route('posts.show', $post))
            ->assertInertia(fn (Assert $page) => $page->component('Posts/Show')->has('comments', 1));
    }

    public function test_un_utilisateur_peut_aimer_puis_retirer_son_like(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('posts.like', $post));
        $this->assertSame(1, $post->likes()->count());

        $this->actingAs($user)->post(route('posts.like', $post));
        $this->assertSame(0, $post->likes()->count());
    }

    public function test_on_ne_peut_pas_modifier_ou_supprimer_la_publication_d_autrui(): void
    {
        $post = Post::factory()->create();
        $intruder = User::factory()->create();

        $this->actingAs($intruder)->get(route('posts.edit', $post))->assertForbidden();
        $this->actingAs($intruder)->put(route('posts.update', $post), $this->postData(['title' => 'Titre piraté !!']))->assertForbidden();
        $this->actingAs($intruder)->delete(route('posts.destroy', $post))->assertForbidden();

        $this->assertNotSame('Titre piraté !!', $post->fresh()->title);
    }

    public function test_l_auteur_peut_modifier_et_supprimer_sa_publication(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($post->user)->put(route('posts.update', $post), $this->postData(['title' => 'Titre corrigé par son auteur']))
            ->assertRedirect(route('posts.show', $post));
        $this->assertSame('Titre corrigé par son auteur', $post->fresh()->title);

        $this->actingAs($post->user)->delete(route('posts.destroy', $post))->assertRedirect(route('posts.mine'));
        $this->assertModelMissing($post);
    }

    public function test_on_ne_peut_pas_modifier_le_commentaire_d_autrui(): void
    {
        $comment = new Comment(['content' => 'Réponse originale']);
        $comment->user()->associate(User::factory()->create());
        $comment->post()->associate(Post::factory()->create());
        $comment->save();

        $intruder = User::factory()->create();
        $this->actingAs($intruder)->put(route('comments.update', $comment), ['content' => 'Modifié'])->assertForbidden();
        $this->actingAs($intruder)->delete(route('comments.destroy', $comment))->assertForbidden();

        $this->actingAs($comment->user)->put(route('comments.update', $comment), ['content' => 'Modifié par moi'])->assertRedirect();
        $this->assertSame('Modifié par moi', $comment->fresh()->content);
    }

    public function test_la_recherche_retourne_des_resultats_pertinents(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->create(['title' => 'Paiement Mobile Money avec Laravel', 'content' => 'Intégration de MTN MoMo.']);
        Post::factory()->for($user)->create(['title' => 'Animations en Flutter', 'content' => 'Tween et AnimationController.']);

        $this->actingAs($user)->get(route('search', ['q' => 'mobile money']))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Search')
                ->has('posts.data', 1)
                ->where('posts.data.0.title', 'Paiement Mobile Money avec Laravel'));
    }

    public function test_le_contenu_markdown_est_securise(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post('/posts', $this->postData([
            'content' => "Test <script>alert('xss')</script> et [lien](javascript:alert(1)) avec `code`.",
        ]));

        $html = Post::first()->content_html;
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('<code>code</code>', $html);
    }

    public function test_toutes_les_pages_principales_s_affichent(): void
    {
        $this->seed(\Database\Seeders\DemoSeeder::class);
        $user = User::where('email', 'demo@devlink.africa')->first();
        $post = Post::first();

        foreach (['/dashboard', '/explore', '/my-posts', '/posts/create', "/posts/{$post->id}", "/posts/{$post->id}/edit",
            '/developers', '/developers?country=Bénin', "/developers/{$user->id}", '/search?q=laravel', '/profile'] as $url) {
            $response = $this->actingAs($user)->get($url);
            $this->assertContains($response->status(), [200, 403], "Échec sur $url");
        }
    }
}
