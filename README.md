# README

## Start with docker

sur les pc CPE, éxecuter docker avec `sudo` (Attention, ne marche pas dans document et bureau)

```bash
docker-compose up -d
```

Au lancement de la base de donnée, le script sql s'execute automatiquement, cependant si le script est mis à jour. Voici la commande permettant de relancer le script:
```bash
docker-compose exec database bash -c 'psql -h localhost -U pgtp -W < /public/bd/acuBD-pgsql.sql'
# puis entrer le mot de passe "tp"
```

Si le docker-compose.yml est mis à jour :
```bash
docker-compose down && docker-compose build --no-cache && docker-compose up -d
```

## Team
Carella Alexis 
Louedec Remi 
Perrono Antoine 
Machillot Eléa 

## Elements notable
Page 404 
Changement de mot de passe

## Documentation
- Site officiel de PHP
- Graficart