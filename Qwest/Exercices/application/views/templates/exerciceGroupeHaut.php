<h1><?php echo $_SESSION["donneesExercice"]["exerciceTitre"]; ?></h1>
    <div class="conteneur">
        <legend>Question: <?php echo $_SESSION["donneesExercice"]["page"]; ?> / <?php echo $_SESSION["donneesExercice"]["nombrePages"]; ?>, <?php echo $_SESSION["question"]->nom; ?></legend>
        <br/>
        <?php echo $_SESSION["groupe"]["Description"]; ?><br/>