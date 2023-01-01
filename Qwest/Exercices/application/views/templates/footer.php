            </div>
        </div>
        <footer>
            <div class="liste">
                <h6>Qui sommes nous?</h6><br/>
                <div>
                    <ul><a href="<?php 
                        if($this->session->permission === "A"){ 
                            echo $_SESSION["LHS"] . "admin/a-propos"; 
                        }
                        else{
                            echo $_SESSION["LHS"] . "connecte/a-propos";
                        }
                        ?>">
                        <li>Notre histoire,
                        Nos anciens sites,
                        Comment a t-on développé ce site?</li>
                    </a></ul>
                </div>
            </div>
            
            <link rel="stylesheet" href="<?php echo $_SESSION["LHS"] . "css/49eeb2c_layout_1.css"; ?>" type="text/css" />
            <link rel="stylesheet" href="<?php echo $_SESSION["LHS"] . "css/25f4475_application_1.css"; ?>" type="text/css" />
            <link rel="stylesheet" href="<?php echo $_SESSION["LHS"] . "css/4327dce_typo_1.css"; ?>" type="text/css" />
            <link rel="stylesheet" href="<?php echo $_SESSION["LHS"] . "css/528c5fb_reset_1.css"; ?>" type="text/css" />
            <link rel="stylesheet" href="<?php echo $_SESSION["LHS"] . "css/c2f103d_menu_1.css"; ?>" type="text/css" />
            
            <script type="text/javascript" src="<?php echo $_SESSION["LHS"] . "js/210cee6_menu_1.js"; ?>"></script>
            <script type="text/javascript" src="<?php echo $_SESSION["LHS"] . "js/9a39151_bouton_1.js"; ?>"></script>
        </footer>
    </body>
</html>
