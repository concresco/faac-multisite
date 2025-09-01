# Metodi per bonifica utenti bot (Multisite WordPress)

Data: 2025-08-29
Percorso: /home/runcloud/webapps/FAAC/memory-bank/

## Convenzioni
- Blog ID → meta ruoli: `pr_{BLOG_ID}_capabilities`
  - Sito root (BLOG_ID 1): `pr_capabilities` e/o `pr_1_capabilities`
- Ruolo vuoto (No role): `meta_value = 'a:0:{}'`
- Sempre: backup TSV prima delle rimozioni, log in `memory-bank/` dopo.

## 1) Conteggio/estrazione per periodo
Esempio: subscribers registrati in agosto 2025 sul sito UK (root).

```bash
wp db query "
SELECT YEAR(u.user_registered) y, COUNT(*) c
FROM pr_users u
JOIN pr_usermeta m
  ON m.user_id=u.ID
 AND m.meta_key IN ('pr_capabilities','pr_1_capabilities')
 AND m.meta_value LIKE '%subscriber%'
WHERE u.user_registered >= '2025-08-01' AND u.user_registered < '2025-09-01'
GROUP BY YEAR(u.user_registered);
"
```

Backup TSV utenti target:
```bash
wp db query "
SELECT u.ID,u.user_login,u.user_email,u.user_registered
FROM pr_users u
JOIN pr_usermeta m
  ON m.user_id=u.ID
 AND m.meta_key IN ('pr_capabilities','pr_1_capabilities')
 AND m.meta_value LIKE '%subscriber%'
WHERE u.user_registered >= '2025-08-01' AND u.user_registered < '2025-09-01'
ORDER BY u.ID;
" | sed '1s/^/ID\tuser_login\tuser_email\tuser_registered\n/' \
> memory-bank/subscribers_aug2025.tsv
```

## 2) Rimozione ruoli vs eliminazione utenti
- Rimuovere ruolo subscriber (solo dal sito):
```bash
wp --url=www.faacentrancesolutions.co.uk user remove-role <USER_ID> subscriber --quiet
```
- Eliminare utenti solo dal sito:
```bash
wp --url=<dominio> user delete <ID...> --yes --quiet
```
- Eliminare utenti a livello network (cancella dal multisito):
```bash
wp user delete <ID...> --network --yes --quiet
```

Batch con log:
```bash
# Sequenziale
while read -r ID; do
  wp --url=<dominio> user delete "$ID" --yes --quiet \
  && echo "deleted $ID" >> memory-bank/delete_<dominio>.log \
  || echo "failed $ID"  >> memory-bank/delete_<dominio>.log;
done < ids.txt

# Batch (max 100 argomenti)
xargs -n100 sh -c 'wp user delete "$@" --network --yes --quiet' sh < ids.txt
```

## 3) Individuare utenti "No role"
Sito root (UK):
```bash
wp db query "
SELECT u.ID,u.user_login,u.user_email,u.user_registered
FROM pr_users u
JOIN pr_usermeta um
  ON um.user_id=u.ID
 AND um.meta_key IN ('pr_capabilities','pr_1_capabilities')
WHERE um.meta_value='a:0:{}'
  AND u.user_registered BETWEEN '2025-08-01' AND '2025-08-31'
ORDER BY u.ID;
" | sed '1s/^/ID\tuser_login\tuser_email\tuser_registered\n/' \
> memory-bank/norole_aug2025.tsv
```

## 4) Rilevare nomi "random" (≥8 lettere, mix maiuscole/minuscole, non Title Case)
Per BLOG_ID={BID} (es. `faac.at` è 9 → `pr_9_capabilities`), opzionale esclusione email per dominio:
```sql
SELECT u.ID, u.user_login, u.user_email,
       fn.meta_value AS first_name,
       ln.meta_value AS last_name,
       cap.meta_value AS capabilities,
       u.user_registered
FROM pr_users u
LEFT JOIN pr_usermeta cap ON cap.user_id=u.ID AND cap.meta_key='pr_{BID}_capabilities'
LEFT JOIN pr_usermeta fn  ON fn.user_id=u.ID  AND fn.meta_key='first_name'
LEFT JOIN pr_usermeta ln  ON ln.user_id=u.ID  AND ln.meta_key='last_name'
WHERE cap.meta_value IS NOT NULL
  AND (cap.meta_value LIKE '%subscriber%' OR cap.meta_value='a:0:{}')
  AND (
    (fn.meta_value REGEXP BINARY '^[A-Za-z]{8,}$'
     AND fn.meta_value REGEXP BINARY '.*[A-Z].*'
     AND fn.meta_value REGEXP BINARY '.*[a-z].*'
     AND fn.meta_value NOT REGEXP BINARY '^[A-Z][a-z]+$')
    OR
    (ln.meta_value REGEXP BINARY '^[A-Za-z]{8,}$'
     AND ln.meta_value REGEXP BINARY '.*[A-Z].*'
     AND ln.meta_value REGEXP BINARY '.*[a-z].*'
     AND ln.meta_value NOT REGEXP BINARY '^[A-Z][a-z]+$')
  )
  -- opzionale: AND u.user_email NOT LIKE '%.at'
ORDER BY u.ID;
```

## 5) Scansione multi-sito con export per dominio
```bash
AGG=memory-bank/suspicious_all_sites.tsv
printf "site\tID\tuser_login\tuser_email\tfirst_name\tlast_name\tcapabilities\tuser_registered\n" > "$AGG"
wp site list --fields=blog_id,domain,path --format=csv | tail -n +2 | while IFS=, read -r BID DOM PATHP; do
  OUT="memory-bank/suspicious_${DOM}.tsv"
  KEYCOND="cap.meta_key='pr_${BID}_capabilities'"
  [ "$BID" = "1" ] && KEYCOND="cap.meta_key IN ('pr_capabilities','pr_1_capabilities')"
  SQL="SELECT u.ID, u.user_login, u.user_email, fn.meta_value AS first_name, ln.meta_value AS last_name, cap.meta_value AS capabilities, u.user_registered
       FROM pr_users u
       LEFT JOIN pr_usermeta cap ON cap.user_id=u.ID AND ${KEYCOND}
       LEFT JOIN pr_usermeta fn  ON fn.user_id=u.ID  AND fn.meta_key='first_name'
       LEFT JOIN pr_usermeta ln  ON ln.user_id=u.ID  AND ln.meta_key='last_name'
       WHERE cap.meta_value IS NOT NULL
         AND (cap.meta_value LIKE '%subscriber%' OR cap.meta_value='a:0:{}')
         AND ((fn.meta_value REGEXP BINARY '^[A-Za-z]{8,}$' AND fn.meta_value REGEXP BINARY '.*[A-Z].*' AND fn.meta_value REGEXP BINARY '.*[a-z].*' AND fn.meta_value NOT REGEXP BINARY '^[A-Z][a-z]+$')
           OR (ln.meta_value REGEXP BINARY '^[A-Za-z]{8,}$' AND ln.meta_value REGEXP BINARY '.*[A-Z].*' AND ln.meta_value REGEXP BINARY '.*[a-z].*' AND ln.meta_value NOT REGEXP BINARY '^[A-Z][a-z]+$'))
       ORDER BY u.ID;"
  wp db query "$SQL" | sed '1s/^/ID\tuser_login\tuser_email\tfirst_name\tlast_name\tcapabilities\tuser_registered\n/' > "$OUT"
  COUNT=$(($(wc -l < "$OUT")-1))
  if [ "$COUNT" -gt 0 ]; then
    wp db query "SELECT '${DOM}' AS site, u.ID, u.user_login, u.user_email, fn.meta_value AS first_name, ln.meta_value AS last_name, cap.meta_value AS capabilities, u.user_registered FROM pr_users u LEFT JOIN pr_usermeta cap ON cap.user_id=u.ID AND ${KEYCOND} LEFT JOIN pr_usermeta fn ON fn.user_id=u.ID AND fn.meta_key='first_name' LEFT JOIN pr_usermeta ln ON ln.user_id=u.ID AND ln.meta_key='last_name' WHERE cap.meta_value IS NOT NULL AND (cap.meta_value LIKE '%subscriber%' OR cap.meta_value='a:0:{}') AND ((fn.meta_value REGEXP BINARY '^[A-Za-z]{8,}$' AND fn.meta_value REGEXP BINARY '.*[A-Z].*' AND fn.meta_value REGEXP BINARY '.*[a-z].*' AND fn.meta_value NOT REGEXP BINARY '^[A-Z][a-z]+$') OR (ln.meta_value REGEXP BINARY '^[A-Za-z]{8,}$' AND ln.meta_value REGEXP BINARY '.*[A-Z].*' AND ln.meta_value REGEXP BINARY '.*[a-z].*' AND ln.meta_value NOT REGEXP BINARY '^[A-Z][a-z]+$')) ORDER BY u.ID;" \
    | tail -n +2 >> "$AGG"
  fi
  echo "$DOM: $COUNT"
done
```

## 6) Verifica finale
- Ricalcolare i conteggi con le stesse query di selezione.
- Controllare da WP Admin che le liste rispecchino i risultati.
- Conservare `*.tsv`, `*_ids.txt`, `delete_*.log` in `memory-bank/`.
