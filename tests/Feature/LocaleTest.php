<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_francais_est_la_langue_par_defaut(): void
    {
        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('locale', 'fr'));
    }

    public function test_un_visiteur_peut_passer_en_anglais(): void
    {
        $this->from('/')->post('/locale', ['locale' => 'en'])->assertRedirect('/');

        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('locale', 'en'));
        $this->get('/login')->assertSee('lang="en"', false);
    }

    public function test_une_langue_non_prise_en_charge_est_refusee(): void
    {
        $this->post('/locale', ['locale' => 'de'])->assertSessionHasErrors('locale');
        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('locale', 'fr'));
    }

    public function test_le_francais_reste_par_defaut_meme_avec_un_navigateur_en_anglais(): void
    {
        $this->withHeader('Accept-Language', 'en-US,en;q=0.9')
            ->get('/')
            ->assertInertia(fn (Assert $page) => $page->where('locale', 'fr'));
    }

    public function test_messages_et_erreurs_traduits_en_anglais(): void
    {
        $this->seed(CategorySeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)->withSession(['locale' => 'en'])
            ->post('/posts', ['title' => 'Court', 'content' => 'x', 'type' => 'question', 'category_id' => Category::first()->id])
            ->assertSessionHasErrors(['title' => 'The title field must be at least 8 characters.']);

        $this->actingAs($user)->withSession(['locale' => 'en'])
            ->post('/posts', [
                'title' => 'How to deploy Laravel on a VPS?',
                'content' => 'Looking for a simple and reliable deployment method.',
                'type' => 'question',
                'category_id' => Category::first()->id,
            ])
            ->assertSessionHas('success', 'Your post is live.');
    }

    public function test_messages_en_francais_par_defaut(): void
    {
        $this->seed(CategorySeeder::class);
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/posts', ['title' => 'Court', 'content' => 'x', 'type' => 'question', 'category_id' => Category::first()->id])
            ->assertSessionHasErrors(['title' => 'Le champ titre doit contenir au moins 8 caractères.']);
    }
}
