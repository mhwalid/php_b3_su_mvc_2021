# Sciences-U - B3 IW - PHP7-8 MVC from scratch Groupe Basile-Wallid

[TOC]

## Note pour le prof 

Nous avions pour idée d'ajouter au MVC, un système d'api automatique comme le fais Api Platform. 

Nous avons donc fais des essais, en lisant un attribut sur les entités et créant des classes préconfigurer pour créer des api.

Mais rien ne c'est montrer concluant. Par manque de temps nous avons donc abandonner le projet.

Nous avons donc décider de réaliser pleins de petites fonctionnalités qui pourrais être utile pour tout type de projet.

Un peu comme Symfony qui intègre et propose pas mal de bibliothèque pour plein de petite fonctionnalité.

## Prérequis :

PHP Version 8.1.0

MySQL Version 5.7.31

Composer 2.10

### **Commande à exécuter **

#### 	**Installer les dépendance **

```
composer install
```

#### 	Remplir la base de donnée 

```shell
php vendor/bin/doctrine orm:validate-schema
php vendor/bin/doctrine orm:schema-tool:create
```

#### Pour Faire marcher l'envoie de mail 

1) Ajouter le dossier sendMail.zip disponible dans le répertoire git /ressources, puis le dézipper dans /wamp64
   *Note : On utilise un mailServer configurer avec google : smtp_server=smtp.gmail.com . On force un sender : php.b3.su.mvc.2021@gmail.com* 

2) Dans le php.ini modifier ces lignes : 
   	SMTP = localhost
   	smtp_port = 25
   	sendmail_path ="[VotreRepertoire]\wamp64\sendMail\sendmail.exe"

#### Pour la conversion de csv en xlsx 

1) Ajouter l'extension php "php_gd.dll" dans [VotreRepertoirePhp]/ext

2. Décommenter la ligne extension=gd dans votre php.ini

Vous voila prêt pour commencer :)

## Architecture

Nous avons ajouter des dossiers pour mieux structurer le projet.

**/public** : Tout les documents stocker via l'application sont stocker dans le dossier public/ . L'objectif est de les rendre disponible partout. On est également sur que tout le monde aura le droit de lire ou écrire dedans car c'est le point d'entrée de notre application.

**/src/services:** Contient des class de service qui ont pour but d'effectuer des taches global au projet.

**/src/Utils/FormError :** Une class pour gérer les erreurs dans les formulaires.

**/templates :** Contient un fichier base.html.twig pour héritage des Template twig (voir la section Twig) .



Pour commencer nous avons décider implémenter un envoie de mail paramétrable et facilement utilisable partout dans le projet.

## Envoie de mail paramétrable

### Principe 

Il est possible d'envoyer un mail. Cela consiste à envoyer un mail tout simple avec un mail d'envoie, un nom d'envoie, un mail de réception
un sujet et un message. Ce sont des valeurs qui sont obligatoires. Il est également possible d'y ajouter des options supplémentaires : 

- Ajouter un reply-to
- Ajouter un list de CC (Liste de mail)
- Ajouter un template twig predefinis 
- Ajouter un pièce Jointes dans le mail 
- Enregistrer le mail en BDD

### Technique 

Pour l'envoie de mail on crée donc un service qui s'appele MailService.php.
**Il contient deux fonctions public :**

1 - **La méthode sendmail() {}**

La fonction initialise un header qui est l'entête de notre mail. Cette entête est découper en deux partie :

- Une entête contenant les informations basics du mail qui sont initialiser via la méthode private _getMailHeaders(). 

  ```php
  private function _getMailHeaders(string $fromName, string $fromMail, string $replyToMail , array $cc): string
  {
      $headers = "MIME-Version: 1.0\r\n";
      $headers .='From:'.$fromName.'<'.$fromMail.'>'."\n";
      $headers .='Reply-To: '.$replyToMail."\n";
      if (!empty($cc)) {
          foreach ($cc as $c) {
              $headers .= 'Cc: '. $c . "\r\n";
          }
      }
      return $headers;
  }
  ```

  *Note : MIME-Version permet d’indiquer que le contenu du message est formaté en MIME c'est un standard pour l'envoie de mail*.

- L'autre partie de entête qui est initialiser directement dans la fonction et qui permet de renseigner le type de contenus du mail. Dans le cas de l'envoie d'un mail simple il suffit de mettre du texte ou html. Dans le cas d'un mail avec une pièce jointe on dois mettre du multipart.

1-2 - On voulais également inclure la possibilité d'envoyer des Template dans le mail. Pour permettre d'envoyer des mail structurer. Pour cela on vérifie si le mail dois être envoyer avec un Template prédéfinis. Si oui, on lis le fichier html correspondant. 

Pour récupérer plus simplement le répertoire de Template et l'adapter à tout les OS et projet on crée une méthode : 

```php
  /**
     * @param $fileTemplateName
     * @return string
     */
    private function _getWdMailTemplate($fileTemplateName): string {
        $projectDirectory = dirname(__DIR__);
        // Pour les machines sous linux
        if (PHP_OS === 'Linux') {
            return $projectDirectory . '/templates/mail/custom/' . $fileTemplateName;
        } else {
            // Pour Windows
            return $projectDirectory . '\\templates\\mail\\custom\\' . $fileTemplateName;
        }
    }
```

Le but de la méthode est de récupérer le répertoire du projet pour avoir accès à l'ensemble des fichiers. Puis en fonction de l'OS de l'utilisateur on vient rajouter le répertoire qui nous intéresse ici Template/mail/custom où les Template de mail custom doivent être ranger. On y a créer un Template pour tester que cela marche bien. 

1-3 - Puis on envoie le mail grâce à la méthode native de PHP qui est mail() (https://www.php.net/manual/fr/function.mail.php)

1-4 - Enfin si l'option pour ajouter le mail en BDD est a true alors on ajoute le mail en BDD via la méthode private _createMailInBDD(). On levé des exceptions si cela échoue. *(Voir section suivis de mail)*

**2 - La méthode sendMailWithAttach(){}**
Cette fonction fais la même chose que la première. Cependant elle permet d'envoyer une pièce jointe. Nous sommes partie du principe que c'était une fonctionnalité indispensable dans l'envoie d'un mail. Description : 

- On ajoute un boundary au header de la requête qui est une clé aléatoire de limite pour définir un séparateur et on précise l'encodage en UTF-8 pour éviter les problèmes d'encodage :

```php
// Clé aléatoire de limite pour definir un separateur
$boundary = md5(uniqid(microtime(), TRUE));

// Headers
$headers = $this->_getMailHeaders($fromName, $fromMail, $replyToMail, $cc);
$headers .= 'Content-Type: multipart/mixed;boundary='.$boundary."\r\n";
$headers .='Content-Transfer-Encoding: 8bit' ."\r\n";
```

- Puis on regarde si la pièce jointe est dans le dossier public/mailAttachment qui est censé contenir toute les pièce jointe importer. Le but du répertoire est de savoir exactement où ce trouve les fichiers pièce jointe où les enregistre et où les récupérer.

- Puis on lis le fichier et l'encode proprement pour éviter les problèmes lors de l'envoie : 

  ```php
  if (file_exists($fullFileName))
          {
              $fileType = filetype($fullFileName);
              $fileSize = filesize($fullFileName);
  
              $handle = fopen($fullFileName, 'r');
              if (!$handle) {
                  die('File '.$fileName.'can t be open');
              }
              $content = fread($handle, $fileSize);
              $content = chunk_split(base64_encode($content));
              $f = fclose($handle);
              if (!$f) {
                  die('File '.$fileName.'can t be close');
              }
              ...
  ```

- Enfin on envoie le mail et enregistre en BDD

Cela étant fais nous avons voulu crée des formulaire pour l'envoie de mail.

**3- Mail Controller** 

Pour ce faire nous avons créer un Controller MailController avec deux routes : 

-  **Accéder au formulaire :** #[Route(path: "/mail", httpMethod: "GET", name: "showMail")]
-  **Lors du submit du formulaire :** #[Route(path: "/mail/create", httpMethod: "POST", name: "createMail")]


Dans le Controller la méthode la plus importante est createMail(). Analysons cette méthode : 

3-1 Tout d'abord on vérifie que les informations sont bien envoyé. On vérifie également que les informations obligatoires sont envoyé et quelles ne contiennent pas d'erreur : 

```php
// Verification des champs obligatoires
$tabObligatoire = array($_POST['senderName'], $_POST['senderMail'] , $_POST['receiverMail'], $_POST['subject'],$_POST['message']);

$errorEmpty = $error->validateEmpty($tabObligatoire);
```

Pour gérer les erreurs dans le formulaire on a décider de créer une class src/Utils/FormError.php qui valide les données rentrer. Si il y a une erreur il retourne un message d'erreur. L'objectif était de faire une class réutilisable et de pas surcharger le Controller de méthode qui ne lui corresponde pas forcement et qui peuvent être utilise dans d'autre Controller.

*Note : Toute les erreurs générer sont renvoyer au formulaire avec un message d'erreur spécifique.*

3-2 On vérifie ensuite si la pièce jointe est importée. Si elle l'est alors on utilise la méthode _uploadFile(): 

Cette méthode à pour but de vérifier si il y a des erreurs, et si le fichier respect les règles (image ou PDF) et pas trop grand.

L'objectif est éviter d'envoyer des fichier qui pourrais être des scripts ou des exécutable. Aussi des fichiers trop lourd qui ferais planter l'application.
Si les conditions sont respectées on déplace le fichier importé dans le répertoire ou sont censé être les fichier en PJ -> public/mailAttachment 

```php
// Vérifie le type du fichier
if(in_array($filetype, $allowed)){
     $uploadDirFile = $this->_getWdAttachmentDocument() . $fileName;
     //var_dump($pathFile);
     if(file_exists($uploadDirFile)){
           unlink($uploadDirFile);
      }
     // Creation et moove du fichier importer dans le repertoire
     move_uploaded_file($_FILES["formFile"]["tmp_name"], $uploadDirFile);
```

*Note : A noter que si les conditions ne sont pas respecter cela nous retourne sur la page du formulaire, mais les valeurs renseigner sur le premier formulaire sont retourner dans leurs input. Le but est de ne pas d'avoir tout retaper les informations du formulaire et de voir notre faute.*

3-3 Quand 3-1 et 3-2 sont vérifier et valider on s'occupe de transformer les adresses mails renseignées dans le CC en tableau, pour pouvoir les envoyer plus facilement après. On utilise la méthode _fillArrayCC(), qui explode la chaine de l'input cc et qui à chaque ',' associe l'email à un index du tab . On voulais vraiment pouvoir renseigner plusieurs adresses en CC pour permettre l'envoie du mail à plusieurs personnes : 

```php
private function _fillArrayCC(): array
    {
        $arrayMailCc = [];
        if (!empty($_POST['ccMail'])) {
            $arrayMailCc = explode(",", $_POST['ccMail']);
        }
        return $arrayMailCc;
    }

// createMail()
..
$arrayMailCc = $this->_fillArrayCC();
..
```

3-4 On vérifie ensuite les erreurs dans le formulaire, comme l'exactitude du format des adresses mails ou la taille du texte dans les inputs (ne pas excéder 200 caractères ). Cela grâce à la méthode _validateFormError().

3-5 Si il y a aucune erreur alors on envoie les mails avec ou sans pièce jointe en fonction du formulaire. Puis cela nous renvoie sur une la page mail/successSendMail.html.twig qui confirme le bon envoie du mail.

**4 - Template **
Nous avons créer un dossier mail/ dans le Template ou nous avons mis le fichier formulaire dans /form/createMail.html.twig.
*Note : Pour faciliter le projet et le rendre plus esthétique nous avons utiliser Bootstrap sur l'ensemble du projet*.
Le formulaire contient tout les champs possible pour l'envoie du mail. Nous n'avons juste pas mis une sélection de Template personnalisé par manque de temps, mais cela pourrais être développer.



## Suivis des mail

Apres avoir réaliser l'envoie du mail, nous avons trouver cela dommage d'envoyer des mails et de ne pas garder une trace de ceci. Cela pourrais être très utile d'avoir un suivis des mails pour savoir quand, à qui, pourquoi et comment à était envoyer les mails.

Pour cela nous avons donc décider de sauvegarder les mail en BDD avec doctrine. Nous avons créer une entité Mail qui as tout les champs des mails que l'on envoyer + un champs dateSend qui permet de savoir la date d'envoie.

Tout ce passe dans le service, juste après l'envoie du mail comme on l'as vu précédemment.

Pour pouvoir visualiser tout ca nous avons fais une méthodes avec un route dans le Mail Controller : 
#[Route(path: "/mails", httpMethod: "GET", name: "showMails")]

Son but est de récupérer tout les mails en BDD via un entityManager et de les envoyer au Template de vue : mail/showMails.html.twig
Ce Template utilise un tableau Bootstrap que l'on remplis en bouclant sur nos mails via la balise {% for %} de twig : 

```twig
{% for mail in mails %}
            <tr>
                <th scope="row">{{ mail.fromName }}</th>
                <td>{{ mail.fromMail }}</td>
                <td>{{ mail.toMail }}</td>
                <td>{{ mail.subject }}</td>
                <td>{{ mail.message }}</td>
                <td>
                    {% for c in mail.cc %}
                        {{ c }} /
                    {% endfor %}
                </td>
                <td>{{ mail.fileName }}</td>
                <td>{{ mail.replyToMail }}</td>
                <td>{{ mail.dateSend | date('d/m/Y') }}</td>
            </tr>
 {% endfor %}
```



## Convert File 

Nous avons également décidé d'intégrer un moyen de convertir des fichiers. Nous avons développer qu'une seul conversion pour le moment celle de csv en xlsx. Si nous avons choisis cette fonctionnalité et ce type de fichier, c'est car c'est l'une des plus fréquente conversion de fichier. De plus les conversion de fichier est quelque chose de courant dans des projets de développement web.

Pour ce faire nous avons créer un service ConvertCsvToExcelService qui contient la méthode convertCsvToExel() : 

```php
public function convertCsvToExel(string $fileName): string
    {
        $spreadsheet = new Spreadsheet();
        $reader = new Csv();

        // On met les parametres pour lire le csv
        $reader->setDelimiter(';');
        $reader->setEnclosure('"');
        $reader->setSheetIndex(0);

        // Charger le fichier csv et generer le fichier xls
        $spreadsheet = $reader->load($fileName);
        $fileNameWithoutExtension = explode('.' , $fileName)[0];
        $writer = new Xlsx($spreadsheet);
        $writer->save($fileNameWithoutExtension.'.xlsx');

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $fileNameWithoutExtension.'.xlsx';
    }
```

Cette méthode utilise la bibliothèque PhpSpreadsheet qui est l'une des plus réputé dans ce genre de conversion.

La méthode lis un csv et charge le fichier pour ensuite générer un xlsx. On donne le même nom au xlsx qu'au csv.

Il enregistre ce fichier au même endroit ou ce trouve le csv c'est a dire dans public/convertFile qui comme public/mailAttachment est un dossier comportant les fichier converti.

Pour utiliser le service on fais un Controller ConvertFileController. Comme pour le mail on fais un Template pour afficher un formulaire qui contient un input file.

Ce Controller contient 2 routes : 

- **Accéder au formulaire** #[Route(path: "/convert/csv", httpMethod: "GET", name: "showConvertCsv")] 
- **Effectuer la conversion après le submit** #[Route(path: "/convert/csv/create", httpMethod: "POST", name: "createConvertCsv")]

La méthode de conversion est très simple : 

 	1) On upload et vérifie si il y a des erreurs pendant l'upload du fichier
 	2) On récupérer le fichier upload dans public/convertFile 
 	3) On utilise le service de conversion de fichier csv
 	4) Puis on force le téléchargement du fichier dans la page grâce au DownloadFileService *(Voir partie DownloadFile suivante)*

## Download File 

Apres avoir réaliser la conversion de fichier, nous nous sommes rendu compte qu'il pourrais être intéressant de créer un service pour télécharger des fichier, qu'ils soient stockés en local dans le projet ou via une url http.

Pour ce faire on a créer le service DownloadFileService : 

**Celui ci contient :** 

- downloadLocalFile() qui utilise la fonction header() de PHP. Le but est de renvoyer une HTTP méthode qui télécharge sur la page de l'utilisateur le fichier converti.

```php
public function downloadLocalFile(string $filePath) {
    if (file_exists($filePath)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
    }
}
```

- downloadExterneFile() qui utilise curl une bibliothèque pour le transfert de donnée en ligne (https://curl.se/). Elle contient une initialisation de curl. l'enregistrement du fichier en local puis téléchargement de celui ci. On a également une validation de l'url envoyer avec filter_var de php . (https://www.php.net/manual/fr/function.filter-var.php) qui filtre une variable avec un filtre spécifier : 

```php
..
if (filter_var($url, FILTER_VALIDATE_URL) === false) {
    $error = 'Url Invalid';
    return $error;
}
..
```

Tout comme Mail et ConvertFile nous avons réalisé un Controller DownloadController : 

- **Accéder au formulaire** #[Route(path: "/download", httpMethod: "GET", name: "showDownload")]
-  **Effectuer la conversion après le submit** #[Route(path: "/download/create", httpMethod: "POST", name: "createDownload")]

*Note : le principe reste le même*

## Authentification/Login 

Comme nous voulions continuer le projet nous avons réaliser le début d'une authentification simple, il manque la gestion de session et token *(de base nous voulions mais nous n'avons pas eu le temps de finir)* Nous avons donc fais un simple login / registrer sans même une gestion des erreurs. 

Pour l'authentification on hash le mots de passe pour pas le stocker en brut dans la BDD à l'aide de la fonction : 

```php
private function _cryptPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => 10]);
    }
```

Pour le login on regarde le user qui correspond à l'adresse mail rentrée par l'utilisateur, puis on vérifie que le mots de passe est bon : 

```php
private function _isPasswordValid(User $user, string $plainPassword): bool
    {
        return password_verify($plainPassword, $user->getPassword());
    }
```

Tout est disponible dans RegisterController et AuthentificationController

Par la suite le système pourrais être amplement améliorer.

## Test : 

Nous avons également fais des test sur le "mvc de base" que nous avions réaliser en cours.

![image-20211214024306585](C:\Users\basil\AppData\Roaming\Typora\typora-user-images\image-20211214024306585.png)

## Héritage Twig + Pages Utilitaire 

Pour utiliser et Bootstrap via un cdn dans l'ensemble du projet et alléger le code dans les Template On utilise l'héritage de Template twig.

Le principe est d'avoir un fichier nous servant de base , le base.html.twig.

On lui donner la structure d'un fichier html classique, mais on définie des {% block %} qui sont des endroit ou le code peut être surcharger.

**On définis :** 

- Un block pour entête du html
- Un block pour les titres des pages 
- Un block pour le contenus 
- Un block pour le footer de la page.

Ainsi dans toute nos pages si l'on souhaite ajouter un nouveau titre, il nous suffit étendre de la base avec {% extends 'base.html.twig' %}
Puis de redéfinir notre {% block title %}Titre de la page{% endblock %}

**Exemple :** 

```twig
{% extends 'base.html.twig' %}

{% block title %}{{ title }}{% endblock %}

{% block content %}
  <div class="text-center">
    <h4 class="display-4">4<span style="color: #e34545">0</span>4</h4>
    <h5 class="display-5">Oups! Page introuvable</h5>
    <p>Désolé mais la page que vous recherchez n'existe pas</p>
    <a class="stretched-link" href="/">Retour à la page d'accueil</a>
  </div>
{% endblock %}
```

**Page Utilitaires :** 

Nous avons réaliser une page accueil regroupant la liste des fonctionnalités développer. Plus pratique pour l'utilisateur

Puis nous avons fais une page custom pour les pages not found (voir exemple plus haut). Ca donne un charme une identité

## Point a améliorer et autres recherches 

- Ajouter plusieurs pièces jointes au mail
- Ajouter les Template préenregistre dans le form du mail
- Url pas trouver dans download file url
- Gestion des erreurs dans login et registrer
- Ajouter des moyens de conversion différents
- Upload de fichier généraliser
- Variable Global pour accéder au répertoire qui contiennent des fichiers partage dans public
- Créer une class request pour récupérer les valeurs d'une requetés.
- Faire des redirections sur des routes