function trouverZoneErreur(champ) {
  var zone = champ.parentNode.querySelector('.erreur-champ');
  if (!zone) {
    zone = document.createElement('div');
    zone.className = 'erreur-champ';
    champ.parentNode.appendChild(zone);
  }
  return zone;
}

function afficherErreur(champ, message) {
  trouverZoneErreur(champ).textContent = message;
  if (message) {
    champ.classList.add('is-invalid');
  } else {
    champ.classList.remove('is-invalid');
  }
}

function dateValide(valeur) {
  return /^\d{4}-\d{2}-\d{2}$/.test(valeur) && !isNaN(new Date(valeur).getTime());
}

function verifierChamp(champ) {
  var regle = champ.dataset.regle;
  var valeur = champ.value.trim();
  var form = champ.form;
  var message = '';

  if (champ.dataset.optionnel && valeur === '') {
    afficherErreur(champ, '');
    return true;
  }

  if (regle === 'nom') {
    if (!/^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,50}$/.test(valeur)) {
      message = 'Ce champ doit contenir de 2 à 50 lettres.';
    }
  } else if (regle === 'email') {
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(valeur)) {
      message = 'Adresse e-mail invalide.';
    }
  } else if (regle === 'telephone') {
    if (!/^[0-9]{8}$/.test(valeur)) {
      message = 'Le téléphone doit contenir 8 chiffres.';
    }
  } else if (regle === 'motdepasse') {
    if (champ.value.length < 8 || !/[A-Z]/.test(champ.value) || !/[0-9]/.test(champ.value)) {
      message = 'Au moins 8 caractères, une majuscule et un chiffre.';
    }
  } else if (regle === 'confirmation') {
    var original = document.getElementById(champ.dataset.champ);
    if (original && champ.value !== original.value) {
      message = 'La confirmation ne correspond pas au mot de passe.';
    }
  } else if (regle === 'montant') {
    var montant = parseFloat(valeur.replace(',', '.'));
    if (!/^\d+([.,]\d{1,2})?$/.test(valeur) || isNaN(montant) || montant < 1 || montant > 10000) {
      message = 'Le montant doit être compris entre 1 et 10000 DT.';
    }
  } else if (regle === 'choix') {
    if (champ.type === 'radio') {
      if (!form.querySelector('input[name="' + champ.name + '"]:checked')) {
        message = 'Veuillez faire un choix.';
      }
    } else if (valeur === '') {
      message = 'Veuillez faire un choix.';
    }
  } else if (regle === 'requis') {
    if (champ.value === '') {
      message = 'Ce champ est obligatoire.';
    }
  } else if (regle === 'texte') {
    var min = parseInt(champ.dataset.min || '2', 10);
    var max = parseInt(champ.dataset.max || '255', 10);
    if (valeur.length < min || valeur.length > max) {
      message = 'Ce champ doit contenir de ' + min + ' à ' + max + ' caractères.';
    }
  } else if (regle === 'message') {
    if (valeur.length > 500) {
      message = 'Le message ne doit pas dépasser 500 caractères.';
    }
  } else if (regle === 'date') {
    if (!dateValide(valeur)) {
      message = 'Date invalide.';
    }
  } else if (regle === 'datefin') {
    var debut = document.getElementById(champ.dataset.champ);
    if (!dateValide(valeur)) {
      message = 'Date invalide.';
    } else if (debut && dateValide(debut.value) && valeur <= debut.value) {
      message = 'La date de fin doit être après la date de début.';
    }
  } else if (regle === 'url') {
    try {
      var url = new URL(valeur);
      if (url.protocol !== 'http:' && url.protocol !== 'https:') {
        message = 'Veuillez saisir une URL valide (http:// ou https://).';
      }
    } catch (e) {
      message = 'Veuillez saisir une URL valide (http:// ou https://).';
    }
  } else if (regle === 'image') {
    if (champ.files && champ.files.length > 0) {
      var fichier = champ.files[0];
      var extension = fichier.name.split('.').pop().toLowerCase();
      if (['jpg', 'jpeg', 'png', 'webp'].indexOf(extension) === -1) {
        message = 'Seules les images jpg, png ou webp sont acceptées.';
      } else if (fichier.size > 2 * 1024 * 1024) {
        message = 'L\'image ne doit pas dépasser 2 Mo.';
      }
    }
  }

  afficherErreur(champ, message);
  return message === '';
}

document.addEventListener('DOMContentLoaded', function () {
  var formulaires = document.querySelectorAll('form[novalidate]');

  formulaires.forEach(function (form) {
    var champs = form.querySelectorAll('[data-regle]');

    champs.forEach(function (champ) {
      var evenement = (champ.type === 'file' || champ.tagName === 'SELECT' || champ.type === 'radio') ? 'change' : 'blur';
      champ.addEventListener(evenement, function () {
        verifierChamp(champ);
      });
    });

    form.addEventListener('submit', function (e) {
      var valide = true;
      var premier = null;
      champs.forEach(function (champ) {
        if (champ.disabled) {
          return;
        }
        if (!verifierChamp(champ)) {
          valide = false;
          if (!premier) {
            premier = champ;
          }
        }
      });
      if (!valide) {
        e.preventDefault();
        e.stopImmediatePropagation();
        premier.focus();
      }
    });
  });
});
