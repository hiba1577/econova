function trouverZoneErreur(champ) {
  if (champ.type === 'radio' || champ.type === 'checkbox') {
    var zoneGroupe = document.getElementById('erreur-' + champ.name.replace('[]', ''));
    if (zoneGroupe) {
      return zoneGroupe;
    }
  }
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

function dateEnTexte(date) {
  var mois = String(date.getMonth() + 1).padStart(2, '0');
  var jour = String(date.getDate()).padStart(2, '0');
  return date.getFullYear() + '-' + mois + '-' + jour;
}

function estCache(champ) {
  return champ.disabled || champ.closest('[hidden]') !== null;
}

function verifierChamp(champ) {
  var regle = champ.dataset.regle;
  var valeur = champ.value.trim();
  var form = champ.form;
  var message = '';

  if (estCache(champ)) {
    afficherErreur(champ, '');
    return true;
  }

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
  } else if (regle === 'entier') {
    var minimum = parseInt(champ.dataset.min, 10);
    var maximum = parseInt(champ.dataset.max, 10);
    if (!/^\d+$/.test(valeur) || parseInt(valeur, 10) < minimum || parseInt(valeur, 10) > maximum) {
      message = 'La valeur doit être un nombre entier entre ' + minimum + ' et ' + maximum + '.';
    }
  } else if (regle === 'datefuture') {
    if (!dateValide(valeur)) {
      message = 'Date invalide.';
    } else if (valeur < dateEnTexte(new Date())) {
      message = 'La date doit être aujourd\'hui ou plus tard.';
    }
  } else if (regle === 'expiration') {
    var limite = new Date();
    limite.setMonth(limite.getMonth() + 3);
    if (!dateValide(valeur)) {
      message = 'Date d\'expiration invalide.';
    } else if (valeur < dateEnTexte(limite)) {
      message = 'Le médicament doit expirer au moins 3 mois après aujourd\'hui.';
    }
  } else if (regle === 'case') {
    if (!champ.checked) {
      message = 'Cette case doit être cochée.';
    }
  } else if (regle === 'jours') {
    if (!form.querySelector('input[name="' + champ.name + '"]:checked')) {
      message = 'Veuillez cocher au moins un jour de disponibilité.';
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

function majAffichage(form) {
  form.querySelectorAll('[data-si-nom]').forEach(function (bloc) {
    var coche = form.querySelector('input[name="' + bloc.dataset.siNom + '"]:checked');
    bloc.hidden = !(coche && coche.value === bloc.dataset.siValeur);
  });

  var type = form.querySelector('input[name="type_don"]:checked');
  var categorie = form.querySelector('input[name="categorie"]:checked');
  var restreindre = type !== null && type.value === 'physique' && categorie !== null &&
    (categorie.value === 'sang' || categorie.value === 'medicament');

  form.querySelectorAll('select[data-restreindre]').forEach(function (select) {
    select.querySelectorAll('option').forEach(function (option) {
      if (option.value !== '' && !option.dataset.sante) {
        option.hidden = restreindre;
        option.disabled = restreindre;
      }
    });
    if (select.selectedIndex > 0 && select.options[select.selectedIndex].disabled) {
      select.value = '';
    }
  });
  form.querySelectorAll('[data-aide-sante]').forEach(function (aide) {
    aide.hidden = !restreindre;
  });
}

function initialiserCompteur(zone) {
  var compteur = document.createElement('div');
  compteur.className = 'compteur-caracteres';
  compteur.setAttribute('aria-live', 'polite');
  zone.parentNode.insertBefore(compteur, zone.nextSibling);
  var maj = function () {
    compteur.textContent = zone.value.length + ' / ' + zone.dataset.compteur + ' caractères';
    compteur.classList.toggle('depasse', zone.value.length > parseInt(zone.dataset.compteur, 10));
  };
  zone.addEventListener('input', maj);
  maj();
}

document.addEventListener('DOMContentLoaded', function () {
  var formulaires = document.querySelectorAll('form[novalidate]');

  formulaires.forEach(function (form) {
    var champs = form.querySelectorAll('[data-regle]');

    if (form.querySelector('[data-si-nom]')) {
      form.querySelectorAll('input[type="radio"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
          majAffichage(form);
        });
      });
      majAffichage(form);
    }

    form.querySelectorAll('[data-compteur]').forEach(initialiserCompteur);

    champs.forEach(function (champ) {
      var evenement = (champ.type === 'file' || champ.tagName === 'SELECT' || champ.type === 'radio' || champ.type === 'checkbox') ? 'change' : 'blur';
      champ.addEventListener(evenement, function () {
        verifierChamp(champ);
      });
    });

    form.addEventListener('submit', function (e) {
      var valide = true;
      var premier = null;
      champs.forEach(function (champ) {
        if (estCache(champ)) {
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
