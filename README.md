# Factur-X Symfony Project

Ce projet Symfony permet de créer des factures au format **Factur-X** (XML standard européen pour factures électroniques) et de générer des PDF correspondants intégrant ces XML. Il utilise **Doctrine ORM**, **Dompdf** pour le rendu PDF et le package **Atgp\FacturX** pour l'intégration Factur-X.

---

## Fonctionnalités

- Création de factures via formulaire web.
- Gestion des lignes de factures, avec :
  - Produits, description, quantités, prix HT/TVA/TTC.
  - Caractéristiques produits.
  - Allowances/Charges (remises ou frais supplémentaires).
  - Notes par ligne.
- Génération de fichiers XML Factur-X valides.
- Génération de PDF correspondant, intégrant le XML Factur-X.
- Persistance des factures et lignes en base de données.

---

## Installation

1. **Cloner le projet**
   ```bash
   git clone <repo_url>
   cd <project_folder>
   ```

2. **Installer les dépendances avec Composer**
   ```bash
   composer install
   ```

3. **Configurer la base de données dans .env**
   ```ini
   DATABASE_URL="mysql://db_user:db_pass@127.0.0.1:3306/db_name"
   ```

4. **Créer la base de données et les tables**
   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Créer le dossier de stockage des factures**
   ```bash
   mkdir -p factures
   chmod 777 factures
   ```

---

## Utilisation

1. **Lancer le serveur Symfony**
   ```bash
   symfony server:start
   ```
   ou
   ```bash
   php -S 127.0.0.1:8000 -t public
   ```

2. **Accéder à l'application**
   ```arduino
   http://127.0.0.1:8000/invoice/new
   ```

3. **Remplir le formulaire de facture et ajouter des lignes.**

4. **Après soumission, le projet :**
   - Persiste la facture et ses lignes en base.
   - Génère le fichier XML Factur-X dans `factures/facture_<N°.xml>`.
   - Génère le PDF correspondant dans `factures/facture_<N°.pdf>`.
---

## Structure du projet

- `src/Controller/InvoiceController.php` : Controller pour gérer le formulaire et la génération XML/PDF.
- `src/Entity/Invoice.php` : Entité Doctrine représentant la facture.
- `src/Entity/InvoiceLine.php` : Entité Doctrine représentant les lignes de facture.
- `src/Service/InvoiceXmlGenerator.php` : Service pour générer un XML Factur-X valide.
- `src/Service/FacturxService.php` : Service pour intégrer le XML dans le PDF avec le package Atgp\FacturX.
- `templates/invoice/new.html.twig` : Formulaire de création de facture.
- `templates/invoice/pdf.html.twig` : Template HTML pour générer le PDF.

---

## Dépendances principales

- Symfony 6+
- Doctrine ORM
- Dompdf
- Atgp\FacturX (Factur-X writer)

---

## Conseils & bonnes pratiques

- Toujours vérifier que les champs `netPrice`, `grossPrice`, `quantity` et `allowances.amount` soient des `float`.
- Créer le dossier `factures` avec les permissions d’écriture pour que les fichiers XML et PDF puissent être générés.
- Valider le XML généré avec un outil XSD pour s’assurer de la conformité Factur-X.
- Pour personnaliser le PDF, modifier `templates/invoice/pdf.html.twig`.

---

## Licence

Ce projet est distribué sous licence MIT.