<?php
// le sous arbre
$sousArbre=array('sup'=>5,'inf'=>10);

// calcul de la longueur du sous arbre
$l=$sousArbre['sup']-$sousArbre['inf']+1;
				
// On déplace le sous arbre dans une table temporaire, et on le supprime de l'arbre
mysql_query('INSERT INTO table_arbre_temp SELECT * FROM table_arbre WHERE inf>='.$sousArbre['inf'].' AND sup<='.$sousArbre['sup']);
mysql_query('DELETE FROM table_arbre WHERE inf>='.$sousArbre['inf'].' AND sup<='.$sousArbre['sup']);

// Le pére a qui on va tout ajouter
$requete=mysql_query('SELECT * FROM table_arbre WHERE id='.$idpere);
$pere=mysql_fetch_assoc($requete);

// Renumérotation de l'arbre
mysql_query('
	UPDATE table_arbre SET    
		sup=sup - '.$l.'
	WHERE  sup > '.$sousArbre['sup'].' AND sup<'.$pere['sup']
);
mysql_query('
	UPDATE table_arbre SET    
		inf=inf - '.$l.'
	WHERE  inf > '.$sousArbre['sup'].' AND inf<'.$pere['sup']
);

// On calcul la différence entre la pseudo borne sup du pére et la borne inf du sous arbre => renumérotation du sous arbre
$calcul=$pere['sup']-$sousArbre['inf']-$l;
mysql_query('UPDATE table_arbre_temp SET inf=inf+'.$calcul.',sup=sup+'.$calcul);

// On rajoute le sous arbre a l'arbre
mysql_query('INSERT INTO table_arbre SELECT * FROM table_arbre_temp');

// On vide la table temporaire
mysql_query('TRUNCATE TABLE table_arbre_temp');
?>
