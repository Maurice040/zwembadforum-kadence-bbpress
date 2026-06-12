# Zwembadforum Kadence bbPress

Beheerbare frontendlaag voor bbPress op Kadence.

## Wat deze plugin doet

- Laadt alleen op bbPress/forum-schermen.
- Geeft forumoverzichten een card/list layout.
- Maakt topic replies rustiger en beter scanbaar.
- Geeft topicpagina’s een aparte vraag-en-antwoord layout.
- Verbetert mobiele weergave zonder bbPress templates te overschrijven.
- Voegt optioneel `ugc nofollow` toe aan externe links in topic/reply content.
- Dequeuet optioneel de bbPress editor JS voor uitgelogde bezoekers.
- Stylet optioneel de bestaande Bouwzelfjezwembad bannerpositie onder de vraag.
- Heeft een instellingenpagina onder `Instellingen > Zwembadforum bbPress`.
- Heeft een REST endpoint voor beheer via application password:
  `/wp-json/zf-kadence-bbpress/v1/settings`.
- Heeft een eigen updatechecker op basis van een JSON manifest, zodat toekomstige versies via de normale WordPress plugin-updater kunnen lopen.

## Instellingen

- Kadence forumstyling aan/uit.
- Compactere forumkaarten.
- Bouwzelfjezwembad bannerpositie stylen.
- UGC/nofollow op externe forumlinks.
- bbPress editor JS uitschakelen voor gasten.
- Accentkleuren.
- Maximale forum breedte.
- Update manifest URL.

## Updates zonder zip-upload

Zet een JSON bestand online, bijvoorbeeld in GitHub:

```json
{
  "version": "0.4.1",
  "download_url": "https://github.com/<owner>/zwembadforum-kadence-bbpress/releases/download/v0.4.1/zwembadforum-kadence-bbpress-0.4.1.zip",
  "homepage": "https://zwembadforum.eu",
  "requires": "6.3",
  "requires_php": "7.4",
  "tested": "6.8",
  "changelog": "<ul><li>Korte beschrijving van de update.</li></ul>"
}
```

Vul de raw URL van dat manifest in bij `Update manifest URL`. Daarna ziet WordPress toekomstige releases als normale pluginupdates.

## Versie 0.3

- Frissere Kadence-basis met turquoise accent en warm partneraccent.
- Aparte body classes voor forumindex, topiclijst en topicdetail.
- Sponsorblok onder de vraag wordt visueel onderdeel van de topicflow.
- Topic starter en reacties krijgen een compactere, rustiger auteurkolom.

## Versie 0.4.1

- Testrelease voor de GitHub updater.
- Wist de update-manifest cache wanneer instellingen via REST worden opgeslagen.

## Versie 0.4.2

- Lijnt de kolomtitels van topiclijsten uit met de inhoudskolommen.

## Wat deze plugin niet doet

- Geen datamigratie.
- Geen permalinkwijzigingen.
- Geen bbPress/UM/Style Pack instellingen aanpassen.
- Geen Kadence instellingen overschrijven.
- Geen advertentieplugin verwijderen of vervangen.

## Testvolgorde

1. Upload de plugin zip op staging.
2. Activeer de plugin.
3. Controleer:
   - `/forums/`
   - een forumcategorie
   - een topicpagina
   - mobiel scherm
   - ingelogd en uitgelogd
4. Zet daarna pas eventueel asset cleanup/UM afbouw door.
