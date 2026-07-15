# Zwembadforum Kadence bbPress

Beheerbare frontendlaag voor bbPress op Kadence.

## Wat deze plugin doet

- Laadt standaard alleen op bbPress/forum-schermen.
- Kan dezelfde forumstyling ook op de voorpagina laden voor een forumoverzicht-widget.
- Geeft forumoverzichten een card/list layout.
- Maakt topic replies rustiger en beter scanbaar.
- Geeft topicpagina’s een aparte vraag-en-antwoord layout.
- Verbetert mobiele weergave zonder bbPress templates te overschrijven.
- Voegt optioneel `ugc nofollow` toe aan externe links in topic/reply content.
- Dequeuet optioneel de bbPress editor JS voor uitgelogde bezoekers.
- Stylet optioneel de bestaande Bouwzelfjezwembad bannerpositie onder de vraag.
- Kan de advertentie onder de vraag zelf beheren met wisselende banners, zodat de losse `bbp affiliate ads` plugin later uit kan.
- Heeft een instellingenpagina onder `Instellingen > Zwembadforum bbPress`.
- Heeft een leesbaar Forum CSS veld voor kleine hotfixes zonder pluginupdate; de vaste styling wordt cachebaar als extern bestand geladen.
- Heeft een REST endpoint voor beheer via application password:
  `/wp-json/zf-kadence-bbpress/v1/settings`.
- Heeft een eigen updatechecker op basis van een JSON manifest, zodat toekomstige versies via de normale WordPress plugin-updater kunnen lopen.

## Instellingen

- Kadence forumstyling aan/uit.
- Voorpagina forumwidget stylen.
- Compactere forumkaarten.
- Bouwzelfjezwembad bannerpositie stylen.
- Advertenties beheren vanuit deze plugin.
- Veilige advertentiemodus: niet tonen zolang de oude `bbp affiliate ads` plugin actief is.
- Leesbaar bannerveld: `desktop-afbeelding | mobiel-afbeelding | klik-url | alt-tekst | gewicht`.
- UGC/nofollow op externe forumlinks.
- bbPress editor JS uitschakelen voor gasten.
- Accentkleuren.
- Maximale forum breedte.
- Forum CSS, alleen geladen op bbPress/forum-schermen.
- Update manifest URL.

## Updates zonder zip-upload

Zet een JSON bestand online, bijvoorbeeld in GitHub:

```json
{
  "version": "0.5.1",
  "download_url": "https://github.com/<owner>/zwembadforum-kadence-bbpress/releases/download/v0.5.1/zwembadforum-kadence-bbpress-0.5.1.zip",
  "homepage": "https://zwembadforum.eu",
  "requires": "6.3",
  "requires_php": "7.4",
  "tested": "6.8",
  "changelog": "<ul><li>Laadt forumstyling optioneel ook op de voorpagina voor het forumoverzicht in de widget.</li></ul>"
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

## Versie 0.5.0

- Voegt een leesbaar Forum CSS veld toe onder `Instellingen > Zwembadforum bbPress`.
- Laadt deze CSS alleen op bbPress/forum-schermen, na de vaste plugin-CSS.
- Maakt styling-aanpassingen via instellingen en REST mogelijk zonder nieuwe pluginrelease.

## Versie 0.5.1

- Voegt de instelling `Voorpagina forumwidget stylen` toe.
- Laadt de vaste forum-CSS en het Forum CSS veld ook op de voorpagina wanneer deze optie aanstaat.
- Voegt de body class `zf-forum-view-front-widget` toe voor gerichte CSS op de voorpagina.

## Versie 0.6.0

- Voegt een geïntegreerde advertentiemodule toe voor de banner onder de vraag.
- Ondersteunt meerdere banners met gewogen willekeur via een leesbaar instellingenveld.
- Voorkomt standaard dubbele advertenties zolang de oude `bbp affiliate ads` plugin actief is.
- Gebruikt `rel="sponsored nofollow"` voor advertentielinks.

## Wat deze plugin niet doet

- Geen datamigratie.
- Geen permalinkwijzigingen.
- Geen bbPress/UM/Style Pack instellingen aanpassen.
- Geen Kadence instellingen overschrijven.
- Verwijdert of deactiveert de oude advertentieplugin niet automatisch.

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
