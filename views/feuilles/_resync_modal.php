<?php

/** @var array $feuille */
use App\Aelf;
use App\Csrf;
?>
<!-- Modale de resynchronisation AELF : choix du calendrier -->
<div class="modal fade" id="resyncModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="post" action="/app/feuilles/<?= $feuille['id'] ?>/resync">
            <?= Csrf::field() ?>
            <div class="modal-header">
                <h5 class="modal-title">Resynchroniser AELF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body vstack gap-2">
                <p class="text-body-secondary small mb-1">
                    Les lectures sont rechargées depuis AELF : les lectures modifiées seront écrasées.
                </p>
                <?php foreach (Aelf::ZONES as $zone => $libelle): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="zone" value="<?= e($zone) ?>"
                               id="zone-<?= e($zone) ?>" <?= $zone === 'france' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="zone-<?= e($zone) ?>"><?= e($libelle) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                <button class="btn btn-primary"><i class="bi bi-arrow-repeat"></i> Resynchroniser</button>
            </div>
        </form>
    </div>
</div>
