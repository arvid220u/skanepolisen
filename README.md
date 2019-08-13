# Skånepolisen
Mr Gött Mos vs Fubbickarna — only one can survive.

A real-life intense action game. Skånepolisen, created in 2014 by Melker, Arvid and Simon, was legendary from day 1.

## TODOs

- Always reload after a POST request so that bad things with reloading forms won't happen.
- Deploy.
- Giving up gives error.

## Deployment

On DigitalOcean, using Docker droplet. Clone this repo, configure the `.env` file, check the other environment variables and then start compose in detached mode: `docker-compose -f docker-compose.prod.yml -d up`. Ideally, the database should be more persistent than this, but it's fine.

## Proposed Rule Changes

Implemented: All Fubbicks except the one who caught Mr. GM will be allowed to use their bikes. It will still not be trivial for the Fubbicks to catch Mr. GM, since the ones with the bikes will not know where they are. This becomes a much more fun game, in that the Fubbick who ended up at the same position as Mr. GM no longer only chases him, but more acts as a tracker of them, for the other people to get to know their location.

## Original Game Proposal Document

### Skånepolisen

Scotland Yard-inspirerat spel, fast i verkligheten.

Område: Slottsgatan + kanalen, centralön, exklusive kyrkogården

Personer: Mr. Gött Mos^2 vs Fubbickarna

Klassisk och sedan dynamisk

Klassisk:

3 olika färdsätt:
Gång (vit)
Cykel (0, 255, 0)
Cykelräd (7, 18, 243)

Regler: Lite som Scotland Yard. Man får inte cykla genom Palladium. Detektiverna får stå på samma ruta.

Alla får en startposition. Mr. Gött Mos^2 börjar. 

#### Databas

RegisteredUsers (ändras ibland, mestadels statisk)

ActiveUsers (ändras under tiden)

ActiveGame (dynamisk)

GangRelations (statisk)

CykelRelations (statisk)

CykelradRelations (statisk)
