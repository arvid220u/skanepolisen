# Skånepolisen
Mr Gött Mos vs Fubbickarna — only one can survive.

A real-life intense action game. Skånepolisen, created in 2014 by Melker, Arvid and Simon, was legendary from day 1.

To run locally, configure the `SENDGRID_API_KEY` environment variable, and then run `docker-compose up --build`. This will launch the website at http://localhost:8001, phpmyadmin at http://localhost:8080 and the database at port 3306.

## TODOs

- Always reload after a POST request so that bad things with reloading forms won't happen.
- Giving up gives error.

## Deployment

On DigitalOcean, using Docker droplet. Clone this repo, run `cp docker-compose.yml docker-compose.prod.yml` and change the following: the web port to 80, the URL, and the environment variables (the Sendgrid API key can be entered directly into the Compose file, and it is important to modify the username and password of the database). Then start compose in detached mode: `docker-compose -f docker-compose.prod.yml up -d`. Ideally, the database should be more persistent than this, but it's fine.

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
