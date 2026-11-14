<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Données de démonstration : développeurs, discussions, réponses et likes.
 * Tous les comptes ont le mot de passe « DevLink2026 ».
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $devs = [
            ['Démo DevLink', 'demo@devlink.africa', 'Bénin', 'Développeur full-stack Laravel & Vue', ['Laravel', 'Vue.js', 'MySQL', 'Tailwind CSS'], 'Compte de démonstration de DevLink Africa. Je construis des applications web pour les PME de Cotonou.'],
            ['Aïcha Diallo', 'aicha@devlink.africa', 'Sénégal', 'Mobile engineer Flutter', ['Flutter', 'Dart', 'Firebase'], "Développeuse mobile à Dakar. J'aime les apps qui fonctionnent bien même avec une connexion 3G."],
            ['Kwame Mensah', 'kwame@devlink.africa', 'Ghana', 'Data scientist', ['Python', 'Pandas', 'Machine Learning', 'SQL'], 'Data scientist à Accra, je travaille sur la prévision des récoltes pour des coopératives agricoles.'],
            ['Fatou Koné', 'fatou@devlink.africa', "Côte d'Ivoire", 'UI/UX designer', ['Figma', 'Design System', 'UX Research'], "Designer produit à Abidjan. Je défends le design pensé pour l'utilisateur africain."],
            ['Emeka Okafor', 'emeka@devlink.africa', 'Nigeria', 'DevOps & Cloud engineer', ['Docker', 'Kubernetes', 'AWS', 'Linux'], 'Ingénieur DevOps à Lagos. Automatisation, CI/CD et coûts cloud maîtrisés.'],
            ['Yasmine Benali', 'yasmine@devlink.africa', 'Maroc', 'Développeuse React', ['React', 'TypeScript', 'Next.js'], 'Front-end developer à Casablanca, passionnée par la performance web.'],
            ['Jean-Paul Mbarga', 'jeanpaul@devlink.africa', 'Cameroun', 'Backend Java', ['Java', 'Spring Boot', 'PostgreSQL'], "Développeur backend à Douala, spécialisé dans les API de paiement mobile."],
            ['Amina Uwase', 'amina@devlink.africa', 'Rwanda', 'Fondatrice & CTO', ['Node.js', 'Product', 'Leadership'], 'CTO d\'une startup edtech à Kigali. Je partage mon expérience de fondatrice.'],
        ];

        $users = collect($devs)->map(function ($d) {
            $user = User::updateOrCreate(['email' => $d[1]], [
                'name' => $d[0],
                'password' => 'DevLink2026',
                'email_verified_at' => now(),
            ]);
            $user->profile()->updateOrCreate([], [
                'country' => $d[2],
                'headline' => $d[3],
                'skills' => $d[4],
                'bio' => $d[5],
                'github_url' => 'https://github.com/'.strtok($d[1], '@'),
            ]);

            return $user;
        })->values();

        $cat = fn (string $slug) => Category::where('slug', $slug)->value('id');

        $posts = [
            [1, 'flutter-et-dart', 'question', 'Comment gérer le mode hors-ligne dans une app Flutter ?',
                "Mes utilisateurs perdent souvent la connexion. Je cherche une stratégie simple pour :\n\n- stocker les données localement\n- synchroniser quand le réseau revient\n\nVous utilisez **Hive**, **Isar** ou **sqflite** ? Des retours d'expérience en production ?"],
            [2, 'python-et-data', 'resource', 'Notebook : prévoir les prix du maïs avec Prophet',
                "Je partage un notebook qui utilise Prophet pour prévoir les prix du maïs sur les marchés d'Afrique de l'Ouest.\n\n```python\nfrom prophet import Prophet\nm = Prophet(yearly_seasonality=True)\nm.fit(df)\nfuture = m.make_future_dataframe(periods=90)\nforecast = m.predict(future)\n```\n\nLes données viennent de sources publiques. Vos suggestions sont les bienvenues !"],
            [0, 'laravel-et-php', 'question', 'Intégrer le paiement Mobile Money dans Laravel : par où commencer ?',
                "Je dois accepter MTN MoMo et Moov Money sur une boutique Laravel au Bénin. Est-ce que vous passez par un agrégateur (FedaPay, Kkiapay…) ou par les API des opérateurs directement ?\n\nQuels pièges éviter avec les **webhooks** de confirmation ?"],
            [3, 'uiux-design', 'discussion', 'Concevoir pour les petits écrans et les connexions lentes',
                "Quelques principes que j'applique sur tous mes projets :\n\n1. Charger le contenu texte avant les images\n2. Des boutons d'au moins 44px\n3. Toujours un état « chargement » visible\n4. Éviter les polices lourdes\n\nEt vous, quelles sont vos règles ?"],
            [4, 'devops-et-cloud', 'resource', 'Déployer Laravel sur un VPS à 5 $ avec Docker',
                "Voici mon `docker-compose.yml` minimal pour Laravel + MySQL + Nginx :\n\n```yaml\nservices:\n  app:\n    build: .\n    volumes: ['.:/var/www']\n  db:\n    image: mysql:8\n  web:\n    image: nginx:alpine\n    ports: ['80:80']\n```\n\nJ'ajoute ensuite Certbot pour le HTTPS. Questions bienvenues."],
            [5, 'javascript-et-typescript', 'question', 'TypeScript strict : ça vaut le coup sur un petit projet ?',
                "On démarre un projet React à deux développeurs. Activer `strict: true` dès le début ralentit-il trop l'équipe, ou est-ce un investissement rentable ?"],
            [6, 'java-et-spring-boot', 'discussion', "Retour d'expérience : API de paiement à 10 000 transactions/jour",
                "Nous avons migré notre API de paiement vers Spring Boot 3. Les points clés : idempotence des requêtes, files de messages pour les callbacks opérateurs, et beaucoup de logs structurés. Je peux détailler si ça intéresse."],
            [7, 'entrepreneuriat-tech', 'discussion', 'Trouver ses premiers clients B2B quand on est une startup tech africaine',
                "Après 2 ans à Kigali, voici ce qui a marché pour nous : pilotes gratuits limités dans le temps, partenariats avec les incubateurs, et beaucoup de terrain. Partagez vos stratégies !"],
            [2, 'ia-et-machine-learning', 'question', 'Quels modèles de langue pour le wolof, le yoruba ou le fon ?',
                "Je travaille sur un chatbot multilingue. Connaissez-vous des datasets ou des modèles ouverts qui gèrent correctement les langues africaines ?"],
            [5, 'react-et-react-native', 'resource', 'Checklist performance pour une app React Native',
                "- Utiliser `FlatList` au lieu de `ScrollView` pour les longues listes\n- Mémoïser les composants de liste\n- Compresser les images côté serveur\n- Activer Hermes\n\nQue rajouteriez-vous ?"],
        ];

        $created = collect($posts)->map(function ($p, $i) use ($users, $cat) {
            $post = new Post([
                'category_id' => $cat($p[1]),
                'type' => $p[2],
                'title' => $p[3],
                'content' => $p[4],
            ]);
            $post->user()->associate($users[$p[0]]);
            $post->created_at = $post->updated_at = now()->subHours(($i + 1) * 5);
            $post->save();

            return $post;
        });

        $replies = [
            "Merci pour le partage, c'est exactement ce que je cherchais !",
            "J'ai eu le même problème. La solution a été de bien tester les cas limites avant la mise en production.",
            "Très bonne question. Je recommande de commencer petit et d'itérer avec de vrais utilisateurs.",
            "On utilise cette approche depuis six mois, ça fonctionne bien. Attention juste à la documentation.",
            "Est-ce que tu as un lien vers le dépôt GitHub ? J'aimerais contribuer.",
        ];

        foreach ($created as $i => $post) {
            foreach ($users->shuffle()->take(($i % 3) + 1) as $j => $u) {
                if ($u->id === $post->user_id) {
                    continue;
                }
                $comment = new Comment(['content' => $replies[($i + $j) % count($replies)]]);
                $comment->user()->associate($u);
                $comment->post()->associate($post);
                $comment->created_at = $comment->updated_at = $post->created_at->copy()->addMinutes(30 * ($j + 1));
                $comment->save();
            }
            foreach ($users->shuffle()->take(($i * 3) % 7 + 1) as $u) {
                Like::firstOrCreate(['user_id' => $u->id, 'post_id' => $post->id]);
            }
        }
    }
}
