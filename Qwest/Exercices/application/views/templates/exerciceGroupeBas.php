            <?php if($_SESSION["groupe"]["aide"] !== null){ ?>
                <a id="bouton-aide">+ Aide</a>
                <div id="aide" style="display: none;">
                    <?php echo $_SESSION["groupe"]->aide; ?><br/> 
                    <span class="clique-disparait">Cliquez pour faire disparaitre</span>
                </div>
            <?php } ?>

        </div>
    </div>
</div>