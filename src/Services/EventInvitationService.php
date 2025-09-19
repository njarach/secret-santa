<?php

namespace App\Services;

use App\Entity\Event;

class EventInvitationService
{

    public function handleInvitations(mixed $invitations, Event $event)
    {
        // cette méthode doit : trouver les noms et les mails donnés par l'admin lors du remplissage du formulaire, puis
        /* créer et envoyer un mail à chacun des participants en incluant dans le mail l'url avec le token d'accès
        public. ce token peut être utilisé pour créer un compte 'participant' sur l'évènement. Ce token sert avant tout
        à vérifier la participation des participants invités, il s'appelle public join mais pourrait bien s'appeler
        public participant verification token (il a aussi une date d'expiration). Le participant est bien créé dans la base
        mais il n'a pas encore été vérifié. TODO : peut être ajouter une propriété 'verifiedAt' pour les participants
        ou encore joinedAt. Une fois ajouté et ensuite vérifié (son nom et son email sont renseignés par l'admin lors
        de l'ajout mais le participant peut les modifier) il reçoit un mail de bienvenue avec un lien pour accéder à son dashboard
        et son token + nom de l'event. Son token a TODO : ajouter une date d expiration et possibilité pour l'admin de renvoyer un token, voir pour ajouter cloudflare au projet.
        */

    }

}
