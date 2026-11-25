<?php

/*
 * Vérifie que le schéma traduit fidèlement le MCD : relations, unicités et contraintes CHECK.
 * Les CHECK n'existent que sous MySQL (CI) : ces tests sont ignorés sous SQLite (tests locaux par défaut).
 */

use App\Enums\GradingCompany;
use App\Enums\ListingStatus;
use App\Enums\OrderStatus;
use App\Models\Card;
use App\Models\Comment;
use App\Models\Conversation;
use App\Models\Dispute;
use App\Models\Listing;
use App\Models\Message;
use App\Models\Order;
use App\Models\Report;
use App\Models\Review;
use App\Models\Set;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

function skipUnlessMysql(): void
{
    if (DB::getDriverName() !== 'mysql') {
        test()->markTestSkipped('Les contraintes CHECK ne sont posées que sous MySQL.');
    }
}

test('le parcours complet d\'une vente se construit avec les relations du MCD', function () {
    $set = Set::factory()->create(['name' => 'Base Set', 'slug' => 'base-set']);
    $card = Card::factory()->for($set)->create(['name' => 'Charizard', 'number' => '4']);
    $seller = User::factory()->seller()->create();
    $buyer = User::factory()->create();

    // Publier + Vendre + Illustrer
    $listing = Listing::factory()->for($seller, 'seller')->for($card)->withPhotos(2)->create();

    // OUVRIR + Initier + AVOIR + Envoyer
    $conversation = Conversation::factory()->for($listing)->for($buyer, 'buyer')->create();
    Message::factory()->for($conversation)->create();
    Message::factory()->for($conversation)->fromSeller()->create();

    // Recevoir + Ecrire + Répondre
    $question = Comment::factory()->for($listing)->create();
    Comment::factory()->replyTo($question)->for($seller)->create();

    // PASSER + DONNER LIEU + NOTER
    $order = Order::factory()->completed()->for($buyer, 'buyer')->create();
    $listing->forceFill(['order_id' => $order->id, 'status' => ListingStatus::Sold])->save();
    Review::factory()->for($order)->create(['rating' => 5]);

    $listing->refresh();

    expect($card->set->is($set))->toBeTrue()
        ->and($set->cards)->toHaveCount(1)
        ->and($listing->seller->is($seller))->toBeTrue()
        ->and($listing->card->is($card))->toBeTrue()
        ->and($listing->photos->pluck('position')->all())->toBe([1, 2])
        ->and($listing->order->is($order))->toBeTrue()
        ->and($order->listings)->toHaveCount(1)
        ->and($order->buyer->is($buyer))->toBeTrue()
        ->and($order->review->rating)->toBe(5)
        ->and($conversation->messages->pluck('sender_id')->all())->toBe([$buyer->id, $seller->id])
        ->and($question->replies)->toHaveCount(1)
        ->and($question->replies->first()->parent->is($question))->toBeTrue()
        ->and($seller->listings)->toHaveCount(1)
        ->and($buyer->orders)->toHaveCount(1);
});

test('un litige tranché est rattaché à sa commande et au modérateur', function () {
    $dispute = Dispute::factory()->resolved()->create();

    expect($dispute->order->status)->toBe(OrderStatus::Disputed)
        ->and($dispute->order->dispute->is($dispute))->toBeTrue()
        ->and($dispute->moderator->resolvedDisputes)->toHaveCount(1);
});

test('un signalement vise une annonce ou un commentaire', function () {
    $onListing = Report::factory()->create();
    $onComment = Report::factory()->forComment()->create();

    expect($onListing->listing)->not->toBeNull()
        ->and($onListing->comment)->toBeNull()
        ->and($onComment->comment)->not->toBeNull()
        ->and($onComment->listing)->toBeNull()
        ->and($onListing->reporter->reports)->toHaveCount(1);
});

// --- Contraintes d'unicité (valables sur tous les SGBD) ---

test('le pseudo est unique (RG-01)', function () {
    User::factory()->create(['username' => 'sacha']);

    User::factory()->create(['username' => 'sacha']);
})->throws(QueryException::class);

test('une commande ne reçoit qu\'un avis (RG-28)', function () {
    $review = Review::factory()->create();

    Review::factory()->for($review->order)->create();
})->throws(QueryException::class);

test('une seule conversation par couple annonce et acheteur (RG-31)', function () {
    $conversation = Conversation::factory()->create();

    Conversation::factory()->create([
        'listing_id' => $conversation->listing_id,
        'buyer_id' => $conversation->buyer_id,
    ]);
})->throws(QueryException::class);

test('un utilisateur ne signale qu\'une fois la même annonce (RG-13)', function () {
    $report = Report::factory()->create();

    Report::factory()->create([
        'reporter_id' => $report->reporter_id,
        'listing_id' => $report->listing_id,
    ]);
})->throws(QueryException::class);

test('une commande n\'a qu\'un litige', function () {
    $dispute = Dispute::factory()->create();

    Dispute::factory()->for($dispute->order)->create();
})->throws(QueryException::class);

// --- Contraintes CHECK (MySQL uniquement) ---

test('un signalement ne peut viser à la fois une annonce et un commentaire (contrainte XT)', function () {
    skipUnlessMysql();

    Report::factory()->create(['comment_id' => Comment::factory()]);
})->throws(QueryException::class);

test('un signalement doit viser quelque chose (contrainte XT)', function () {
    skipUnlessMysql();

    Report::factory()->create(['listing_id' => null]);
})->throws(QueryException::class);

test('le prix d\'une annonce est compris entre 1 € et 10 000 € (RG-09)', function (int $price) {
    skipUnlessMysql();

    Listing::factory()->create(['price' => $price]);
})->with([99, 1_000_001])->throws(QueryException::class);

test('une carte gradée a un organisme et une note, et pas d\'état (RG-07)', function () {
    skipUnlessMysql();

    Listing::factory()->create(['is_graded' => true, 'grading_company' => GradingCompany::Psa, 'grade' => null]);
})->throws(QueryException::class);

test('la note de gradation va de 1 à 10 par demi-points (RG-07)', function (float $grade) {
    skipUnlessMysql();

    Listing::factory()->graded()->create(['grade' => $grade]);
})->with([0.5, 10.5, 8.3])->throws(QueryException::class);

test('une note de gradation à demi-point est acceptée (RG-07)', function () {
    $listing = Listing::factory()->graded()->create(['grade' => 9.5]);

    expect($listing->fresh()->grade)->toBe('9.5');
});

test('l\'avis est une note de 1 à 5 (RG-28)', function (int $rating) {
    skipUnlessMysql();

    Review::factory()->create(['rating' => $rating]);
})->with([0, 6])->throws(QueryException::class);

test('le total d\'une commande est la somme des prix, du port et des frais de protection (RG-16)', function () {
    skipUnlessMysql();

    Order::factory()->create(['subtotal' => 1000, 'shipping_fee' => 200, 'protection_fee' => 120, 'total' => 999]);
})->throws(QueryException::class);

test('un litige tranché porte une décision et un modérateur (RG-26)', function () {
    skipUnlessMysql();

    Dispute::factory()->create(['status' => 'resolved', 'resolved_at' => now()]);
})->throws(QueryException::class);
