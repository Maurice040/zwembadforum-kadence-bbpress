# Code of Conduct voor interne links op Zwembadforum.eu

## Doel

Versterk het interne linknetwerk door bezoekers vanuit bestaande forumberichten naar andere bestaande topics te leiden die op die plek aanvullende, bruikbare informatie geven.

Over het hele forum is ongeveer twee nieuwe links per topic een richtpunt. Dit is een gemiddelde voor het forum, geen minimum per topic en geen quotum. Een topic kan nul, één, twee of soms meer links krijgen. Relevantie voor de bezoeker gaat altijd voor aantallen.

## Leidende regel

Stel een link alleen voor als die op precies die plek ook nuttig en logisch zou zijn voor een echte bezoeker wanneer Google niet bestond. Bij twijfel komt er geen link.

## Bestaande berichten behouden

Openingsposts en bestaande antwoorden zijn door forumgebruikers geschreven. Hun inhoud blijft ongewijzigd.

Een link mag alleen worden toegevoegd door bestaande tekst als anchor te gebruiken. De zichtbare woorden en leestekens blijven exact gelijk. Voeg geen tekst toe, verwijder of herschrijf niets en corrigeer geen spelling.

Niet toegestaan:

- tekst toevoegen om een link mogelijk te maken;
- zinnen, spelling, leestekens of anchors aanpassen;
- nieuwe AI-tekst, SEO-hubs, overzichtspagina's, gerelateerde-onderwerpenblokken of footerlinks maken;
- keyword stuffing of geforceerde links gebruiken.

## Bronnen en bestemmingen beoordelen

- Beoordeel bij een brontopic de volledige discussie: de openingspost en alle replies. Een reply kan een betere linklocatie hebben dan de openingspost.
- Gebruik de forumindex als kandidatenlijst. Begin met lichte gegevens zoals topic-ID, URL, titel, forum en metadata.
- Lees de volledige inhoud van alleen de serieuze targetkandidaten die nodig zijn om relevantie vast te stellen. Voer geen volledige all-vs-all-inhoudsvergelijking uit.
- Kies targets op inhoudelijke samenhang en aanvullende waarde, niet op alleen titel- of keywordoverlap.
- Link alleen naar een bestaand, gepubliceerd forumtopic. Link nooit naar hetzelfde topic als het brontopic.
- Controleer de hele brondiscussie op een bestaande equivalente link voordat je een nieuwe voorstelt.
- Voeg geen onnodige herhaalde links naar hetzelfde target vanuit één discussie toe. Spreid targets waar dat inhoudelijk logisch is en laat dezelfde paar populaire topics niet steeds terugkomen.
- Een GSC-testgroep is nooit een targetquotum. Als een meetgroep wordt gebruikt, rapporteer dan per voorstel of het target daarin zit. Forceer nooit een link naar een URL uit die groep.

## Anchors en markup

- Gebruik een exact bestaand woord of kort tekstfragment dat de bestemming natuurlijk beschrijft.
- Plaats een link niet binnen een bestaande link, URL, HTML-attribuut of ongeschikte markup.
- Behoud alle overige HTML, bbPress-markup en shortcodes. De enige inhoudelijke wijziging is de hyperlink rond de ongewijzigde anchor.

Voorbeeld:

Bestaand:

`Welke warmtepomp heb ik hiervoor nodig?`

Toegestaan, als het target echt aanvullende informatie geeft:

`Welke <a href="TARGET">warmtepomp</a> heb ik hiervoor nodig?`

## Voorstellen beoordelen

Maak elk voorstel controleerbaar met:

- brontopic: titel en URL;
- bronlocatie: post/reply-ID en type;
- exacte anchor met voldoende omliggende bestaande tekst;
- targettopic: titel en URL;
- een korte reden waarom het target de lezer helpt;
- GSC-testgroep: ja of nee, wanneer een meetgroep van toepassing is;
- bevestiging dat er vanuit de brondiscussie nog geen equivalente link bestaat.

## Wijzigingen uitvoeren en controleren

Voer een linkwijziging pas uit nadat de gebruiker de concrete link heeft goedgekeurd. Gebruik de bestaande ondertekende Zwembadforum API Bridge; bouw geen nieuwe bridge, plugin, MCP-server of andere infrastructuur voor dit werk.

Wijzig alleen de goedgekeurde links. Lees na iedere wijziging de opgeslagen bronpost terug en controleer dat de zichtbare inhoud exact gelijk is gebleven, de anchor naar de goedgekeurde URL verwijst en de HTML/bbPress-markup intact is. Controleer de gerenderde pagina waar dat mogelijk is. Stop en rapporteer zodra een controle faalt; ga niet verder met volgende wijzigingen voordat het probleem is opgelost.

## Beslisprincipe

Het doel is een natuurlijk netwerk van nuttige contextuele links over het hele forum. Het doel is niet dat ieder topic twee links krijgt. Als een link vooral om SEO-redenen wordt voorgesteld en geen duidelijke waarde heeft voor de bezoeker, stel hem dan niet voor.
