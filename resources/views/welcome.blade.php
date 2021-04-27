<?php
$rand = random_int(0, 1);
if ($rand == 1) {
        $str = "<html>
        <head>
              <title>403 Forbidden</title>
        </head>
        <body bgcolor=\"white\">
                <center>
              <h1>403 Forbidden</h1>
                </center>
                <hr>
                <center>nginx</center>
        </body>
        </html>";
} else {
        $str = "{\"name\":\"Mouse Logitech G402\",\"origin\":\"Lokal\",\"item_type\":\"ITEM\",\"category_id\":\"786\",\"store_id\":\"27\",\"selling_price\":\"450000\",\"created_by\":\"5\",\"created_by_id\":\"bbrf.marketing@gmail.com\",\"minimal_stock\":\"10\",\"condition\":\"1\",\"description\":\"bla bla blaa\",\"weight\":\"200\",\"weight_unit\":\"GR\",\"picture\":\"/storage/MWsGcwpmvs2faLJOOtyWBeCyE2uCZhJfn7RVp73U.png\",\"picture_two\":\"/storage/MWsGcwpmvs2faLJOOtyWBeCyE2uCZhJfn7RVp73U.png\",\"picture_three\":\"null\",\"picture_four\":\"null\",\"picture_five\":\"null\",\"bahan\":\"gak tau\",\"merk\":\"apple\",\"variant\":[{\"variant_name\":\"hitam\",\"harga\":\"10000\",\"stock\":\"4\",\"picture\":\"/storage/MWsGcwpmvs2faLJOOtyWBeCyE2uCZhJfn7RVp73U.png\"},{\"variant_name\":\"merah\",\"harga\":\"12000\",\"stock\":\"4\",\"picture\":\"/storage/MWsGcwpmvs2faLJOOtyWBeCyE2uCZhJfn7RVp73U.png\"}]}";
        $str = json_decode($str, true);
}

echo is_array($str) ? dd($str) : (str_contains($str, "Forbidden") ? "Yes" : "False");
echo "<br><h1>{$rand}</h1>";
