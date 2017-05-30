# Change Log

## [3.0.4] - 2016-09-30
### Ajouts
- Moyens de paiement : choix du mode d'affichage (module, moyen de paiement, module + moyen de paiement) dans le Back et Front Office
- Activation / Désactivation de l'automatisation des actions Back Office sur les commandes (remboursement automatique...)

### Modifications
- Validation de commande : amélioration de la gestion des erreurs
- Traductions
- CartLocker : suppression à la désinstallation du module
- Configuration : gestion des variables à conserver lors de la désinstallation / réinstallation
- Paiement à l'expédition : affichage bouton capture totale sur la commande uniquement si capture manuelle configurée dans le module

### Corrections
- PrestaShop 1.5 : prise en compte d'un bug PrestaShop de Context bloquant la création de commande pour les versions inférieures à 1.5.5
- Paiement en 3 fois : suppression de la demande remboursement lors de l'annulation des futures échéance
- Ajout de carte dans la configuration du module : non prise en compte du paiement en 3 fois, erreurs à l'ajout

## [3.0.3] - 2016-09-01
### Ajouts
- Installation : enrichissement des messages d'erreur dans le Back Office et alimentation des logs du module
- CartLocker : mécanisme de contrôle pour éventuels cas où plusieurs appels IPN sont traités par PrestaShop pour un même panier

### Modifications
- Installation : ordre des étapes

## [3.0.2] - 2016-07-21
### Ajouts
- Remboursement standard avec bon de réduction : gestion de la saisie d'un montant manuel et adaptation si le remboursement inclut un code promotionnel
- Script d'upgrade à partir de la version 1.9.0 pour création de la colonne '3DS' des cartes et alimentation des valeurs
- Script d'upgrade pour alimentation de la colonne 'initial_amount' si non renseigné pour des commandes utilisant le module dans des versions antérieures

## [3.0.1] - 2016-07-12
### Modifications
- Traductions
- Chemin de template pour le retour boutique suite paiement
- OnePageCheckout : inclusion du CSS dans le template pour prise en compte dans le chargement Ajax

### Corrections
- Paiement en 3 fois : correction de la date de la 3e échéance lors de la réception de l'appel IPN du 2e prélèvement
