<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
{
    $products = [
        [
         'name'=> 'CALAMARI SALE E PEPE',
         'price'=> '9.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'baffi di calamari, farina, cipolla, peperoni, misto spezie',
        ],
        [
         'name'=> 'BAFFI DI CALAMARI IN TEMPURA',
         'price'=> '7.50',
         'category_id'=> 'FRITTURE',
         'ingredients'=> 'calamari, farina, sale',
        ],
        [
         'name'=> 'TIRAMISU',
         'price'=> '4.50',
         'category_id'=> 'DESSERT',
         'ingredients'=> 'crema di mascarpone, caffè, cacao, biscotti savoiardi',
        ],
        [
         'name'=> 'TIRAMISU MATCHA',
         'price'=> '5.50',
         'category_id'=> 'DESSERT',
         'ingredients'=> 'crema di mascarpone, tè matcha, polvere di tè matcha, biscotti savoiardi',
        ],
        [
         'name'=> 'HOSOMAKI FRAGOLA',
         'price'=> '0.00',
         'category_id'=> 'HOSOMAKI FRITTO',
         'ingredients'=> 'RISO, SALMONE, PHILADELPHIA, FRAGOLA',
        ],
        [
         'name'=> 'FIORE D\' ESTATE',
         'price'=> '15.00',
         'category_id'=> 'PIATTI SPECIALI',
         'ingredients'=> 'FIORE DI ZUCCA IN TEMPURA, BURATA, MANGO, GAMBERO ROSSO, UOVA DI LOMPO, SALSA PURE DI MANGO ',
        ],
        [
         'name'=> 'GUNKAN ZUCCHINE E GAMBERETTI',
         'price'=> '5.50',
         'category_id'=> 'GUNKAN',
         'ingredients'=> 'zucchina, gamberetti, surimi, maionese, riso',
        ],
        [
         'name'=> 'INSALATA DI MARE CINESE',
         'price'=> '0.00',
         'category_id'=> 'ANTIPASTI CUCINA',
         'ingredients'=> 'GAMBERETTI, POLIPETTI, CALAMARETTI, RUCOLA, LIMONE',
        ],
        [
         'name'=> 'CANNOLO FUSION',
         'price'=> '3.50',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'sfoglia di involtino, salmone, philadelphia, pistacchio',
        ],
        [
         'name'=> 'TONNO TATAKI',
         'price'=> '10.00',
         'category_id'=> 'PIASTRA',
         'ingredients'=> 'tonno, sesamo, salsa kaizu',
        ],
        [
         'name'=> 'PANNA COTTA CON FRUTTI DI BOSCO',
         'price'=> '4.00',
         'category_id'=> 'DESSERT',
         'ingredients'=> 'panna, colla di pesce, misto di frutti di bosco',
        ],
        [
         'name'=> 'CROCCHETTE DI POLLO',
         'price'=> '5.50',
         'category_id'=> 'FRITTURE',
         'ingredients'=> 'pollo, farina, uova, pangrattato',
        ],
        [
         'name'=> 'GUNKAN ITALIANO',
         'price'=> '6.00',
         'category_id'=> 'GUNKAN',
         'ingredients'=> 'zucchina, burrata, pomodoro sotto olio ',
        ],
        [
         'name'=> 'GAMBERONE',
         'price'=> '9.00',
         'category_id'=> 'PIASTRA',
         'ingredients'=> 'gamberone, sale, pepe',
        ],
        [
         'name'=> 'NIGIRI SALMONE',
         'price'=> '3.00',
         'category_id'=> 'NIGIRI',
         'ingredients'=> 'salmone, riso',
        ],
        [
         'name'=> 'NIGIRI MAGURO',
         'price'=> '4.00',
         'category_id'=> 'NIGIRI',
         'ingredients'=> 'tonno, riso',
        ],
        [
         'name'=> 'NIGIRI SUZUKI',
         'price'=> '3.00',
         'category_id'=> 'NIGIRI',
         'ingredients'=> 'branzino, riso',
        ],
        [
         'name'=> 'NIGIRI POLIPO',
         'price'=> '3.00',
         'category_id'=> 'NIGIRI',
         'ingredients'=> 'polipo, alghe, riso',
        ],
        [
         'name'=> 'NIGIRI EBI',
         'price'=> '3.50',
         'category_id'=> 'NIGIRI',
         'ingredients'=> 'gamberetti cotti, riso',
        ],
        [
         'name'=> 'NIGIRI AMAEBI GAMBERI CRUDI',
         'price'=> '4.00',
         'category_id'=> 'NIGIRI',
         'ingredients'=> 'gamberetti crudi d\'acqua dolce, riso',
        ],
        [
         'name'=> 'NIGIRI AVOCADO',
         'price'=> '3.50',
         'category_id'=> 'NIGIRI',
         'ingredients'=> 'avocado, alghe, riso',
        ],
        [
         'name'=> 'FLAMBE SALMONE SCOTTATO CON GRANELLE DI PISTACCHIO',
         'price'=> '4.50',
         'category_id'=> 'FLAMBE',
         'ingredients'=> 'concentrato di latte, maionese, salmone, riso',
        ],
        [
         'name'=> 'FLAMBE TONNO SCOTTATO CON GRANELLE DI PISTACCHIO',
         'price'=> '5.00',
         'category_id'=> 'FLAMBE',
         'ingredients'=> 'concentrato di latte, maionese, tonno riso',
        ],
        [
         'name'=> 'FLAMBE CRISPY SALMON',
         'price'=> '4.50',
         'category_id'=> 'FLAMBE',
         'ingredients'=> 'concentrato di latte, mix di peperoncini, maionese, miso , polvere di tempura',
        ],
        [
         'name'=> 'FLAMBE CRISPY FRESH',
         'price'=> '4.50',
         'category_id'=> 'FLAMBE',
         'ingredients'=> 'maionese al tartufo, concentrato di latte, mix di peperoncini, miso , polvere di tempura',
        ],
        [
         'name'=> 'SASHIMI SALMONE',
         'price'=> '7.00',
         'category_id'=> 'SASHIMI',
        ],
        [
         'name'=> 'SASHIMI TONNO',
         'price'=> '8.00',
         'category_id'=> 'SASHIMI',
        ],
        [
         'name'=> 'SASHIMI BRANZINO',
         'price'=> '6.50',
         'category_id'=> 'SASHIMI',
        ],
        [
         'name'=> 'SASHIMI POLIPO',
         'price'=> '7.00',
         'category_id'=> 'SASHIMI',
        ],
        [
         'name'=> 'CARPACCIO SALMONE',
         'price'=> '10.00',
         'category_id'=> 'CARPACCI',
         'ingredients'=> 'carpaccio di salmone, salsa ponzu',
        ],
        [
         'name'=> 'CARPACCIO TONNO',
         'price'=> '11.00',
         'category_id'=> 'CARPACCI',
         'ingredients'=> 'carpaccio di tonno, salsa ponzu',
        ],
        [
         'name'=> 'CARPACCIO MISTO DI PESCE',
         'price'=> '11.00',
         'category_id'=> 'CARPACCI',
         'ingredients'=> 'carpaccio di pesce misto, salsa ponzu',
        ],
        [
         'name'=> 'CARPACCIO PESCE BIANCO E OLIO TARTUFO',
         'price'=> '7.00',
         'category_id'=> 'CARPACCI',
         'ingredients'=> 'carpaccio di pesce misto, salsa ponzu',
        ],
        [
         'name'=> 'CARPACCIO POLIPO',
         'price'=> '10.00',
         'category_id'=> 'CARPACCI',
         'ingredients'=> 'carpaccio di pesce misto, salsa ponzu',
        ],
        [
         'name'=> 'CANNOLO CON SPICY SALMON',
         'price'=> '3.50',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'sfoglia di involtino, salmone, philadelphia, pistacchio',
        ],
        [
         'name'=> 'CANNOLO  CON SPICY TUNA',
         'price'=> '4.50',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'sfoglia di involtino, spicy tuna, cipolla croccante',
        ],
        [
         'name'=> 'TARTARE DI SALMONE',
         'price'=> '9.00',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'salmone a cubetti, avocado, salsa fatta in casa “traccia di frutta e soia”',
        ],
        [
         'name'=> 'TARTARE DI TONNO',
         'price'=> '10.00',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'tonno a cubetti, avocado, salsa fatta in casa “traccia di frutta e soia”',
        ],
        [
         'name'=> 'TARTARE MISTO',
         'price'=> '11.00',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'tonno a cubetti, avocado, salsa fatta in casa “traccia di frutta e soia”',
        ],
        [
         'name'=> 'ALGHE GIAPPONESE',
         'price'=> '4.50',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'alghe giapponese, salsa fatta in casa “traccia di frutta e soia”',
        ],
        [
         'name'=> 'INSALATA DI SALMONE CON SALSA MANGO',
         'price'=> '5.50',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'insalata mista con carpaccio di salmone, mango e salsa di mango',
        ],
        [
         'name'=> 'GUNKAN PHILADELPHIA',
         'price'=> '4.50',
         'category_id'=> 'GUNKAN',
         'ingredients'=> 'salmone, philadelphia, riso',
        ],
        [
         'name'=> 'GUNKAN SPICY SALMON',
         'price'=> '4.50',
         'category_id'=> 'GUNKAN',
         'ingredients'=> 'salmone, mix di piccante, maionese, riso',
        ],
        [
         'name'=> 'GUNKAN SPICY TUNA',
         'price'=> '5.00',
         'category_id'=> 'GUNKAN',
         'ingredients'=> 'tonno, mix di piccante, maionese, riso',
        ],
        [
         'name'=> 'GUNKAN AVOCADO',
         'price'=> '4.00',
         'category_id'=> 'GUNKAN',
         'ingredients'=> 'crema guacamole, salmone, riso',
        ],
        [
         'name'=> 'GUNKAN AMA EBI',
         'price'=> '6.00',
         'category_id'=> 'GUNKAN',
         'ingredients'=> 'salmone, gamberi crudi, ponzu, riso',
        ],
        [
         'name'=> 'GUNKAN BURRATA E PISTACCHI',
         'price'=> '5.00',
         'category_id'=> 'GUNKAN',
         'ingredients'=> 'salmone, burrata, pistacchio',
        ],
        [
         'name'=> 'GUNKAN TOBIKO',
         'price'=> '4.00',
         'category_id'=> 'GUNKAN',
         'ingredients'=> 'salmone, uova di pesce volante, ponzu, riso',
        ],
        [
         'name'=> 'GUNKAN IKURA',
         'price'=> '5.00',
         'category_id'=> 'GUNKAN',
         'ingredients'=> 'salmone, uova di salmone, ponzu, riso',
        ],
        [
         'name'=> 'TEMAKI SALMONE',
         'price'=> '4.00',
         'category_id'=> 'TEMAKI',
         'ingredients'=> 'salmone, avocado, insalata, sesamo, alghe, riso',
        ],
        [
         'name'=> 'TEMAKI TONNO',
         'price'=> '5.00',
         'category_id'=> 'TEMAKI',
         'ingredients'=> 'tonno, avocado, insalata, sesamo, alghe, riso',
        ],
        [
         'name'=> 'TEMAKI TEMPURA TEMAKI',
         'price'=> '5.50',
         'category_id'=> 'TEMAKI',
         'ingredients'=> 'gambero in tempura, avocado, insalata, alghe, riso',
        ],
        [
         'name'=> 'TEMAKI EBI TEMAKI',
         'price'=> '5.00',
         'category_id'=> 'TEMAKI',
         'ingredients'=> 'gamberetti cotti, avocado, insalata, sesamo, maionese, alghe, riso',
        ],
        [
         'name'=> 'TEMAKI VEGETARIANO',
         'price'=> '4.00',
         'category_id'=> 'TEMAKI',
         'ingredients'=> 'verdura mista',
        ],
        [
         'name'=> 'URAMAKI MIURA MAKI',
         'price'=> '7.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'salmone cotto, philadelphia, alghe, sesamo, teriyaki, riso',
        ],
        [
         'name'=> 'URAMAKI SAKE AVOCADO',
         'price'=> '7.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'salmone, avocado, sesamo, riso',
        ],
        [
         'name'=> 'URAMAKI SAKE PHILADELPHIA',
         'price'=> '7.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'salmone, philadelphia, avocado, alghe, sesamo, riso',
        ],
        [
         'name'=> 'URAMAKI RAINBOW ROLL',
         'price'=> '7.50',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'misto di pesce, avocado, alghe, riso, tobiko',
        ],
        [
         'name'=> 'URAMAKI MANDORLE',
         'price'=> '7.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'salmone fritto, philadelphia, mandole, alghe, riso, teriyaki',
        ],
        [
         'name'=> 'URAMAKI SPICY SALMON',
         'price'=> '7.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'salmone piccante, maionese, alghe, sesamo, riso',
        ],
        [
         'name'=> 'URAMAKI SPICY TUNA',
         'price'=> '8.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'tonno piccante, maionese, alghe, sesamo, riso',
        ],
        [
         'name'=> 'URAMAKI TUNA KING',
         'price'=> '8.50',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'tonno, avocado, rucola, maionese al tartufo, alghe, riso',
        ],
        [
         'name'=> 'URAMAKI CALIFORNIA',
         'price'=> '6.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'surimi, cetrioli, avocado, maionese, sesamo, alghe, riso',
        ],
        [
         'name'=> 'URAMAKI YASAI ROLL',
         'price'=> '6.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'misto di verdure, alghe, sesamo, riso',
        ],
        [
         'name'=> 'URAMAKI TIGER ROLL',
         'price'=> '8.50',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'gamberi in tempura, salmone, philadelphia, avocado, teriyaki, alghe, riso',
        ],
        [
         'name'=> 'URAMAKI TIGER FLAMBÈ',
         'price'=> '10.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'gamberi in tempura, avocado, salmone, concentrato di latte, maionese, mix di peperoncino, alghe, teriyaki, tobiko, riso',
        ],
        [
         'name'=> 'URAMAKI EBITEN',
         'price'=> '7.50',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'gamberi in tempura, avocado, teriyaki, tobiko , alghe, riso',
        ],
        [
         'name'=> 'URAMAKI ONION',
         'price'=> '7.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'cipolla croccante, salmone, avocado, alghe, maionese piccante, riso',
        ],
        [
         'name'=> 'URAMAKI FISH and CHIPS',
         'price'=> '8.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'pesce bianco fritto, julienne di patatine, maionese al tartufo, alghe, riso',
        ],
        [
         'name'=> 'URAMAKI CHICKEN FLAMBÉ',
         'price'=> '8.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'pollo fritto, concentrato di latte, mix di peperoncino, alghe, maionese, teriyaki, riso',
        ],
        [
         'name'=> 'URAMAKI CHICKEN',
         'price'=> '7.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'pollo fritto, alghe, teriyaki, riso',
        ],
        [
         'name'=> 'HOSOMAKI SALMONE',
         'price'=> '5.00',
         'category_id'=> 'HOSOMAKI',
         'ingredients'=> 'salmone, alghe, riso',
        ],
        [
         'name'=> 'HOSOMAKI TONNO',
         'price'=> '6.00',
         'category_id'=> 'HOSOMAKI',
         'ingredients'=> 'tonno, alghe, riso',
        ],
        [
         'name'=> 'HOSOMAKI GAMBERETTI COTTI',
         'price'=> '5.00',
         'category_id'=> 'HOSOMAKI',
         'ingredients'=> 'gamberetti cotti, alghe, riso',
        ],
        [
         'name'=> 'HOSOMAKI AVOCADO',
         'price'=> '4.00',
         'category_id'=> 'HOSOMAKI',
         'ingredients'=> 'avocado, alghe, riso',
        ],
        [
         'name'=> 'HOSOMAKI SURIMI',
         'price'=> '4.00',
         'category_id'=> 'HOSOMAKI',
         'ingredients'=> 'surimi, alghe, riso',
        ],
        [
         'name'=> 'HOSOMAKI FRITTO SPICY SALMON',
         'price'=> '8.00',
         'category_id'=> 'HOSOMAKI FRITTO',
         'ingredients'=> 'salmone, maionese piccante, alghe, julienne di patatine, teyaki, riso',
        ],
        [
         'name'=> 'HOSOMAKI FRITTO SPICY TUNA',
         'price'=> '9.00',
         'category_id'=> 'HOSOMAKI FRITTO',
         'ingredients'=> 'salmone, tonno, maionese piccante, alghe, julienne di patatine, teyaki, riso',
        ],
        [
         'name'=> 'HOSOMAKI HOSO FRITTO',
         'price'=> '6.00',
         'category_id'=> 'HOSOMAKI FRITTO',
         'ingredients'=> 'salmone, philadelphia, alghe, teyaki, riso',
        ],
        [
         'name'=> 'RAVVIOLI DI VERDURA',
         'price'=> '5.00',
         'category_id'=> 'VAPORIERA',
         'ingredients'=> 'Spaghetti di soia, verdura misto, farina, spinaci ',
        ],
        [
         'name'=> 'WAKAME',
         'price'=> '4.00',
         'category_id'=> 'ANTIPASTI CUCINA',
         'ingredients'=> 'alghe giapponese',
        ],
        [
         'name'=> 'RUCOLA E POLIPO',
         'price'=> '5.50',
         'category_id'=> 'ANTIPASTI CUCINA',
         'ingredients'=> 'rucola, polipo, olio EVO, limone',
        ],
        [
         'name'=> 'INVOLTINO PRIMAVERA',
         'price'=> '3.00',
         'category_id'=> 'ANTIPASTI CUCINA',
         'ingredients'=> 'farina, carota, verza, strutto',
        ],
        [
         'name'=> 'RAVIOLI CINESI',
         'price'=> '5.50',
         'category_id'=> 'VAPORIERA',
         'ingredients'=> 'farina, maiale, verza, cipollotto, soia, aglio, zenzero, spezie miste',
        ],
        [
         'name'=> 'SHAOMAI',
         'price'=> '6.50',
         'category_id'=> 'VAPORIERA',
         'ingredients'=> 'farina, gamberi, strutto, verdure miste, soia spezie miste',
        ],
        [
         'name'=> 'RAVIOLI GIAPPONESI',
         'price'=> '5.50',
         'category_id'=> 'VAPORIERA',
         'ingredients'=> 'farina di riso, gamberi, verza, soia, spezie miste',
        ],
        [
         'name'=> 'EDAMAME',
         'price'=> '4.50',
         'category_id'=> 'VAPORIERA',
         'ingredients'=> 'fagioli di soia, sale',
        ],
        [
         'name'=> 'PANE CINESE',
         'price'=> '3.50',
         'category_id'=> 'VAPORIERA',
         'ingredients'=> 'farina, lievito',
        ],
        [
         'name'=> 'PATATINE FRITTE',
         'price'=> '3.50',
         'category_id'=> 'FRITTURE',
         'ingredients'=> 'patatine, sale',
        ],
        [
         'name'=> 'CHIPS CINESI',
         'price'=> '3.00',
         'category_id'=> 'FRITTURE',
         'ingredients'=> 'farina di riso, aromi ai crostacei',
        ],
        [
         'name'=> 'EBI TEMPURA',
         'price'=> '9.00',
         'category_id'=> 'FRITTURE',
         'ingredients'=> 'farina mista, gamberetti',
        ],
        [
         'name'=> 'TEMPURA DI VERDURA',
         'price'=> '6.50',
         'category_id'=> 'FRITTURE',
         'ingredients'=> 'farina mista, verdura mista',
        ],
        [
         'name'=> 'PANE CINESE FRITTO',
         'price'=> '3.50',
         'category_id'=> 'FRITTURE',
         'ingredients'=> 'farina, lievito',
        ],
        [
         'name'=> 'ANELLI DI CIPOLLA',
         'price'=> '5.00',
         'category_id'=> 'FRITTURE',
         'ingredients'=> 'pastella di farina, cipolla',
        ],
        [
         'name'=> 'RISO BIANCO',
         'price'=> '4.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'riso basmati al vapore',
        ],
        [
         'name'=> 'RISO ALLA CANTONESE',
         'price'=> '5.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'riso basmati, uova, prosciutto cotto, piselli',
        ],
        [
         'name'=> 'RISO CON VERDURE',
         'price'=> '5.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'riso basmati, uova, verdure miste',
        ],
        [
         'name'=> 'RISO MISTO ALLA SOIA',
         'price'=> '9.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'riso basmati, uova, verdura, pollo, manzo, gamberetti',
        ],
        [
         'name'=> 'RISO CON I GAMBERETTI',
         'price'=> '7.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'riso basmati, uova, piselli, gamberetti',
        ],
        [
         'name'=> 'SPAGHETTI DI RISO CON VERDURE',
         'price'=> '6.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'spaghetti di riso, uova, verdure',
        ],
        [
         'name'=> 'SPAGHETTI DI RISO CON MANZO',
         'price'=> '7.50',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'spaghetti di riso, uova, manzo, verdure',
        ],
        [
         'name'=> 'SPAGHETTI DI RISO CON FRUTTI DI MARE',
         'price'=> '9.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'spaghetti di riso, uova, gamberetti, surimi, calamari',
        ],
        [
         'name'=> 'SPAGHETTI DI SOIA CON VERDURA',
         'price'=> '7.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'spaghetti di soia, verdure, soia',
        ],
        [
         'name'=> 'SPAGHETTI DI SOIA CON FRUTTI DI MARE',
         'price'=> '9.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'spaghetti di soia, gamberetti, surimi, calamari',
        ],
        [
         'name'=> 'TAGLIATELLE CON VERDURE',
         'price'=> '8.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'ingredientliatelle, uova, verdure',
        ],
        [
         'name'=> 'TAGLIATELLE CON MANZO',
         'price'=> '9.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'ingredientliatelle, uova, verdure, manzo',
        ],
        [
         'name'=> 'TAGLIATELLE CON FRUTTI DI MARE',
         'price'=> '9.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'ingredientliatelle, uova, verdure, gamberetti, surimi, calamari',
        ],
        [
         'name'=> 'GNOCCHI CINESE CON VERDURA',
         'price'=> '7.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'farina di riso, uova, verdura',
        ],
        [
         'name'=> 'GNOCCHI CINESE MISTO',
         'price'=> '9.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'farina di riso, uova, verdura, pollo, manzo, gamberetti',
        ],
        [
         'name'=> 'UDON CON VERDURE',
         'price'=> '7.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'spaghetti di riso giapponesi, uova, verdura',
        ],
        [
         'name'=> 'UDON CON FRUTTI DI MARE',
         'price'=> '9.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'spaghetti di riso giapponesi, uova, gamberetti, surimi, calamari',
        ],
        [
         'name'=> 'UDON CON MANZO',
         'price'=> '9.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'spaghetti di riso giapponesi, uova, manzo, verdure',
        ],
        [
         'name'=> 'POLLO CON MANDORLE',
         'price'=> '6.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'pollo, mandorle, soia, spezie miste',
        ],
        [
         'name'=> 'POLLO AL LIMONE',
         'price'=> '6.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'pollo, salsa al limone',
        ],
        [
         'name'=> 'POLLO IN AGRODOLCE',
         'price'=> '6.50',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'pollo fritto, pomodoro, farina di mais, piselli, ananas',
        ],
        [
         'name'=> 'POLLO CON FUNGHI E BAMBÙ',
         'price'=> '6.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'pollo, funghi, bambù, soia, spezie miste',
        ],
        [
         'name'=> 'POLLO PICCANTE',
         'price'=> '6.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'pollo, salsa piccante, cipolla, peperoni, spezie miste',
        ],
        [
         'name'=> 'MANZO E CIPOLLOTTI',
         'price'=> '7.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'manzo, cipollotti, zenzero, spezie miste',
        ],
        [
         'name'=> 'MANZO E PATATE',
         'price'=> '7.50',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'manzo, patate, soia, spezie miste',
        ],
        [
         'name'=> 'MANZO CON FUNGHI E BAMBÙ',
         'price'=> '7.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'manzo, funghi, bambù, soia, misto spezie',
        ],
        [
         'name'=> 'MANZO PICCANTE',
         'price'=> '7.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'manzo, salsa piccante, soia, peperoni, cipolla',
        ],
        [
         'name'=> 'GAMBERETTI AL LIMONE',
         'price'=> '7.50',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'gamberetti, salsa limone',
        ],
        [
         'name'=> 'GAMBERI IN AGRODOLCE',
         'price'=> '8.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'gamberetti fritti, pomodoro, farina di mais, piselli, ananas',
        ],
        [
         'name'=> 'GAMBERETTI PICCANTI',
         'price'=> '7.50',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'gamberetti, salsa piccante, peperoni, piselli',
        ],
        [
         'name'=> 'GAMBERETTI SALE E PEPE',
         'price'=> '8.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'gamberetti fritti, farina, cipolla, peperoni, misto spezie',
        ],
        [
         'name'=> 'POLLO MARINATO',
         'price'=> '5.00',
         'category_id'=> 'PIASTRA',
         'ingredients'=> 'pollo, soia, spezie varie',
        ],
        [
         'name'=> 'GAMBERETTI ALLA PIASTRA',
         'price'=> '7.00',
         'category_id'=> 'PIASTRA',
         'ingredients'=> 'gamberetti, spezie misto',
        ],
        [
         'name'=> 'RAVIOLI ALLA PIASTRA',
         'price'=> '6.00',
         'category_id'=> 'PIASTRA',
         'ingredients'=> 'farina, maiale, verza, cipollotto, soia, aglio, zenzero, spezie miste',
        ],
        [
         'name'=> 'SHAOMAI ALLA PIASTRA',
         'price'=> '7.00',
         'category_id'=> 'PIASTRA',
         'ingredients'=> 'farina, gamberi, strutto, verdure miste, soia spezie miste',
        ],
        [
         'name'=> 'SALMONE ALLA PIASTRA',
         'price'=> '9.00',
         'category_id'=> 'PIASTRA',
         'ingredients'=> 'salmone, burro alle spezie',
        ],
        [
         'name'=> 'BAFFI DI CALAMARI IN SALSA BBQ',
         'price'=> '9.00',
         'category_id'=> 'PIASTRA',
         'ingredients'=> 'calamari, salsa piccante, salsa bbq cinese',
        ],
        [
         'name'=> 'PETTO DI POLLO',
         'price'=> '6.00',
         'category_id'=> 'PIASTRA',
         'ingredients'=> 'petto di pollo, olio EVO, sale',
        ],
        [
         'name'=> 'GERMOGLI DI SOIA',
         'price'=> '4.50',
         'category_id'=> 'CONTORNI',
         'ingredients'=> 'germogli di soia alla patedela',
        ],
        [
         'name'=> 'PATATE ALLA CINESE',
         'price'=> '4.50',
         'category_id'=> 'CONTORNI',
         'ingredients'=> 'julienne di patate, aceto e peperoncino',
        ],
        [
         'name'=> 'TORTA GIAPPONESE',
         'price'=> '6.50',
         'category_id'=> 'DESSERT',
         'ingredients'=> 'uova, burro, formaggio spalmabile, farina, zucchero',
        ],
        [
         'name'=> 'GELATO FRITTO',
         'price'=> '5.50',
         'category_id'=> 'DESSERT',
         'ingredients'=> 'pastella di farina, gelato di vaniglia, pan di spagna, lievito, cioccolato',
        ],
        [
         'name'=> 'INFANZIA',
         'price'=> '5.50',
         'category_id'=> 'DESSERT',
         'ingredients'=> 'concentrato di latte, farina, uova, lievito',
        ],
        [
         'name'=> 'MOCHI',
         'price'=> '5.50',
         'category_id'=> 'DESSERT',
         'ingredients'=> 'farina di riso, gelato di gusti misti',
        ],
        [
         'name'=> 'GELATO CREMA DI BIANCANEVE',
         'price'=> '4.00',
         'category_id'=> 'DESSERT',
         'ingredients'=> 'crema ',
        ],
        [
         'name'=> 'FRUTTA FRESCA',
         'price'=> '5.50',
         'category_id'=> 'DESSERT',
         'ingredients'=> 'frutta di stagione',
        ],
        [
         'name'=> 'FRUTTA CARAMELLATA',
         'price'=> '0.00',
         'category_id'=> 'DESSERT',
        ],
        [
         'name'=> 'ACQUA 50CL',
         'price'=> '2.00',
         'category_id'=> 'BEVANDE',
        ],
        [
         'name'=> 'MENU MIX SUSHI 19PZ',
         'price'=> '0.00',
         'category_id'=> 'MENU MIX SUSHI',
        ],
        [
         'name'=> 'ACQUA FRIZZANTE 75CL',
         'price'=> '3.00',
         'category_id'=> 'BEVANDE',
        ],
        [
         'name'=> 'ACQUA NATURALE DA 75 CL',
         'price'=> '3.00',
         'category_id'=> 'BEVANDE',
        ],
        [
         'name'=> 'COCA ZERO LATTINA 33CL',
         'price'=> '2.50',
         'category_id'=> 'BEVANDE',
        ],
        [
         'name'=> 'COCA 33CL',
         'price'=> '2.50',
         'category_id'=> 'BEVANDE',
        ],
        [
         'name'=> 'FANTA 33CL',
         'price'=> '2.50',
         'category_id'=> 'BEVANDE',
        ],
        [
         'name'=> 'SPRITE 33CL',
         'price'=> '2.50',
         'category_id'=> 'BEVANDE',
        ],
        [
         'name'=> 'THE PESCA 33CL',
         'price'=> '2.50',
         'category_id'=> 'BEVANDE',
        ],
        [
         'name'=> 'THE LIMONE 33CL',
         'price'=> '2.50',
         'category_id'=> 'BEVANDE',
        ],
        [
         'name'=> 'THE CALDO GELSOMINO 33CL',
         'price'=> '4.50',
         'category_id'=> 'BEVANDE',
        ],
        [
         'name'=> 'THE CALDO TÈ VERDE 33CL',
         'price'=> '4.50',
         'category_id'=> 'BEVANDE',
        ],
        [
         'name'=> 'THE CALDO TÈ MATCHA 33CL',
         'price'=> '4.50',
         'category_id'=> 'BEVANDE',
        ],
        [
         'name'=> 'SAKE PRUGNA',
         'price'=> '5.50',
         'category_id'=> 'CAFFETTERIA ',
        ],
        [
         'name'=> 'ACQUA 50CL',
         'price'=> '1.50',
         'category_id'=> 'BEVANDE',
        ],
        [
         'name'=> 'SAPPORO',
         'price'=> '7.50',
         'category_id'=> 'BIRRE',
        ],
        [
         'name'=> 'ASAHI 33ML',
         'price'=> '5.00',
         'category_id'=> 'BIRRE',
        ],
        [
         'name'=> 'BIRRA CINESE 66cl',
         'price'=> '4.50',
         'category_id'=> 'BIRRE',
        ],
        [
         'name'=> 'BIRRA SPINA',
         'price'=> '5.00',
         'category_id'=> 'BIRRE',
        ],
        [
         'name'=> 'ICHNUSA 50 ML',
         'price'=> '5.00',
         'category_id'=> 'BIRRE',
        ],
        [
         'name'=> 'VECCHIO AMARO DEL CAPO',
         'price'=> '3.00',
         'category_id'=> 'AMARI',
        ],
        [
         'name'=> 'AMARO LUCANO',
         'price'=> '3.00',
         'category_id'=> 'AMARI',
        ],
        [
         'name'=> 'AMARO MONTENEGRO',
         'price'=> '3.00',
         'category_id'=> 'AMARI',
        ],
        [
         'name'=> 'FERNET BRANCA',
         'price'=> '3.00',
         'category_id'=> 'AMARI',
        ],
        [
         'name'=> 'AVERNA',
         'price'=> '3.00',
         'category_id'=> 'AMARI',
        ],
        [
         'name'=> 'SAKE',
         'price'=> '5.50',
         'category_id'=> 'CAFFETTERIA ',
        ],
        [
         'name'=> 'GRAPPA DI ROSE',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'GRAPPA DI BAMBU',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'GRAPPA DI RISO',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'GRAPPA DEL NONINO',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'SCAMPI E PASSIONFRUIT',
         'price'=> '15.00',
         'category_id'=> 'PIATTI SPECIALI',
         'ingredients'=> 'Scampi, servito con passion fruit FRESCO',
        ],
        [
         'name'=> 'SASHIMI MISTO S1',
         'price'=> '15.00',
         'category_id'=> 'PIATTI SPECIALI',
         'ingredients'=> 'salmone, tonno, branzino, scampi, gamberi rossi, capesante',
        ],
        [
         'name'=> 'GAMBERO ROSSO',
         'price'=> '15.00',
         'category_id'=> 'PIATTI SPECIALI',
         'ingredients'=> 'Gamberi , burrata',
        ],
        [
         'name'=> 'BAO con PULLEDPORK',
         'price'=> '15.00',
         'category_id'=> 'PIATTI SPECIALI',
         'ingredients'=> 'pane cinese, maiale al vapore ',
        ],
        [
         'name'=> 'GEWURZTRAMINER ST MICHAEL EPPAN',
         'price'=> '24.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'RIBOLLA GIALLA',
         'price'=> '24.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'SPUMANTE MONTELVINI PROMOSSO',
         'price'=> '16.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'COLLE DEL GELSO IGT',
         'price'=> '12.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'PIGNOCCO VERDICCHIO DOC',
         'price'=> '18.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'TENUTA GIGLIO CERASUOLO DOP',
         'price'=> '16.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'PECORINO',
         'price'=> '18.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'ALBA LUNA CUVEE',
         'price'=> '16.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'SANMARTINO PROSECCO BRUT',
         'price'=> '16.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'COCA COLA SPINA 25 cl',
         'price'=> '3.00',
         'category_id'=> 'SPINA',
        ],
        [
         'name'=> 'COCA COLA SPINA 0.5L',
         'price'=> '5.00',
         'category_id'=> 'SPINA',
        ],
        [
         'name'=> 'COCA COLA SPINA 1L',
         'price'=> '8.00',
         'category_id'=> 'SPINA',
        ],
        [
         'name'=> 'VINO FERMO 0,25L',
         'price'=> '4.00',
         'category_id'=> 'SPINA',
        ],
        [
         'name'=> 'VINO FERMO 0,5L',
         'price'=> '7.00',
         'category_id'=> 'SPINA',
        ],
        [
         'name'=> 'VINO FERMO 1L',
         'price'=> '12.00',
         'category_id'=> 'SPINA',
        ],
        [
         'name'=> 'VINO FRIZZANTE 0,25L',
         'price'=> '4.00',
         'category_id'=> 'SPINA',
        ],
        [
         'name'=> 'VINO FRIZZANTE 0,5L',
         'price'=> '7.00',
         'category_id'=> 'SPINA',
        ],
        [
         'name'=> 'VINO FRIZZANTE 1L',
         'price'=> '12.00',
         'category_id'=> 'SPINA',
        ],
        [
         'name'=> 'HEINEKEN PICCOLO 0,33L',
         'price'=> '4.50',
         'category_id'=> 'SPINA',
        ],
        [
         'name'=> 'HEINEKEN MEDIA 0,5L',
         'price'=> '6.00',
         'category_id'=> 'SPINA',
        ],
        [
         'name'=> 'SORBETTO',
         'price'=> '3.50',
         'category_id'=> 'DESSERT',
         'ingredients'=> 'NO LATTOSIO NO GLUTINE ',
        ],
        [
         'name'=> 'CAFFE DECAFFEINATO',
         'price'=> '2.00',
         'category_id'=> 'CAFFETTERIA ',
        ],
        [
         'name'=> 'ORZO',
         'price'=> '2.00',
         'category_id'=> 'CAFFETTERIA ',
        ],
        [
         'name'=> 'GINSENG',
         'price'=> '2.00',
         'category_id'=> 'CAFFETTERIA ',
        ],
        [
         'name'=> 'CAFFE',
         'price'=> '2.00',
         'category_id'=> 'CAFFETTERIA ',
        ],
        [
         'name'=> 'MULLER THURGAU',
         'price'=> '18.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'OFFIDA PASSERINA',
         'price'=> '18.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'GEWURZTRAMINER KENDERMANNS',
         'price'=> '22.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'CHERRI D\'ACQUAVIVA ANCELLA',
         'price'=> '15.00',
         'category_id'=> 'VINO ROSÈ',
        ],
        [
         'name'=> 'ROSATO CENTOVIE',
         'price'=> '16.00',
         'category_id'=> 'VINO ROSÈ',
        ],
        [
         'name'=> 'BRANCA MENTA',
         'price'=> '3.00',
         'category_id'=> 'AMARI',
        ],
        [
         'name'=> 'BAILEYS',
         'price'=> '3.00',
         'category_id'=> 'AMARI',
        ],
        [
         'name'=> 'JAGERMEISTER',
         'price'=> '3.00',
         'category_id'=> 'AMARI',
        ],
        [
         'name'=> 'PAMPERO',
         'price'=> '3.00',
         'category_id'=> 'AMARI',
        ],
        [
         'name'=> 'JAMESON',
         'price'=> '3.50',
         'category_id'=> 'VARIE',
        ],
        [
         'name'=> 'BORGHETTI',
         'price'=> '3.00',
         'category_id'=> 'AMARI',
        ],
        [
         'name'=> 'BASSANO DEL GRAPPA 24 CARATI ORO',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'SAMBUCA MOLINARI',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'GRAPPA VENETA',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'BEEFEATER',
         'price'=> '3.00',
         'category_id'=> 'AMARI',
        ],
        [
         'name'=> 'GRAPPA RISERVA',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'VARNELLI',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'BASSANO DEL GRAPPA CLASSICA',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'GRAPPA DI RISO CINESE',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'GRAPPA DI ROSE CINESE',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'SPUMANTE BRUT',
         'price'=> '15.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'TAKOYAKI',
         'price'=> '0.00',
         'category_id'=> 'FRITTURE',
         'ingredients'=> 'FARINA, ERBA CIPOLLINA, MAIONESE GIAPPONESE, FARINA, POLIPO, PESCE ESICATO',
        ],
        [
         'name'=> 'RUCOLA SALMON',
         'price'=> '0.00',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'RUCCOLA, SALSA RUCCOLA, SALMONE ',
        ],
        [
         'name'=> 'MANGO SALMON',
         'price'=> '0.00',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'MANGO, SALMONE, SALSA PURE DI MANGO',
        ],
        [
         'name'=> 'SPIDER ROLL',
         'price'=> '0.00',
         'category_id'=> 'PIATTI SPECIALI',
         'ingredients'=> 'GRANCHIO INTERO SENZA GUSCIO FRITTO IN TEMPURA ',
        ],
        [
         'name'=> 'FLOWER ROLL',
         'price'=> '0.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'FIORE DI ZUCCA IN TEMPURA, SALMONE, AVOCADO, SALSA MANGO ',
        ],
        [
         'name'=> 'GREEN TIGER',
         'price'=> '0.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'GAMBERO IN TEMPURA, FIORE DI ZUCCA IN TEMPURA, MANGO, SALSA TEYAKI ',
        ],
        [
         'name'=> 'PISTACCHIO ROLL',
         'price'=> '0.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'PESTO DI PISTACCHIO, SALMONE, AVOCADO, BASILICO, FORMAGGIO, AGLIO, OLIO D OLIVA',
        ],
        [
         'name'=> 'SALMON THAI',
         'price'=> '0.00',
         'category_id'=> 'NULLO',
        ],
        [
         'name'=> 'TUNA THAI',
         'price'=> '0.00',
         'category_id'=> 'NULLO',
        ],
        [
         'name'=> 'FRESH ROLL',
         'price'=> '0.00',
         'category_id'=> 'NULLO',
        ],
        [
         'name'=> 'INSALATA DI SESAMO',
         'price'=> '0.00',
         'category_id'=> 'NULLO',
        ],
        [
         'name'=> 'CALICE DI VINO BIANCO FERMO',
         'price'=> '3.00',
         'category_id'=> 'SPINA',
        ],
        [
         'name'=> 'CALICE DI VINO BIANCO FRIZZANTE',
         'price'=> '3.00',
         'category_id'=> 'SPINA',
        ],
        [
         'name'=> 'UKIYO YUZU GIN',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'ETSU GIN GIAPPONESE',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'GRAPPA NONINO',
         'price'=> '3.00',
         'category_id'=> 'GRAPPE',
        ],
        [
         'name'=> 'Altro',
         'price'=> '-1.00',
         'category_id'=> 'VARIE',
        ],
        [
         'name'=> 'NIGIRI DI ANGUS',
         'price'=> '15.00',
         'category_id'=> 'PIATTI SPECIALI',
         'ingredients'=> 'carne di angus e maionese allo zafferano, riso',
        ],
        [
         'name'=> 'URAMAKI ANGUS 8PZ',
         'price'=> '15.00',
         'category_id'=> 'PIATTI SPECIALI',
         'ingredients'=> 'dentro ebi tempura sopra carne di angus e scottato, teyaki, maionese allo zafferano e senape',
        ],
        [
         'name'=> 'BRANZINO A VAPORE',
         'price'=> '0.00',
         'category_id'=> 'PIATTI SPECIALI',
         'ingredients'=> 'brazino al vapore con salsa di soia e accetto ',
        ],
        [
         'name'=> 'ANATRA',
         'price'=> '15.00',
         'category_id'=> 'PIATTI SPECIALI',
         'ingredients'=> 'anatra in salsa centenaria di soia e spezie, pastella e salsa aceto e soia',
        ],
        [
         'name'=> 'MANZO STUFATO',
         'price'=> '15.00',
         'category_id'=> 'PIATTI SPECIALI',
         'ingredients'=> 'manzo stufato in salsa centenaria  di soia e spezie cinesi, salsi di aceto e soia',
        ],
        [
         'name'=> 'TOFU PICCANTE',
         'price'=> '0.00',
         'category_id'=> 'ANTIPASTI CUCINA',
         'ingredients'=> 'maiale macinato, salsa piccante cipolla, peperone, tofu, erba cipollina, spezie varie',
        ],
        [
         'name'=> 'MISO',
         'price'=> '0.00',
         'category_id'=> 'ANTIPASTI CUCINA',
         'ingredients'=> 'alghe giapponese, tofu, miso',
        ],
        [
         'name'=> 'ZUPPA AGRO PICCANTE',
         'price'=> '0.00',
         'category_id'=> 'ANTIPASTI CUCINA',
         'ingredients'=> 'uova, orecchie di mare, bambù, funghi cinesi, erba cipollina, spezie piccanti',
        ],
        [
         'name'=> 'ZUPPA DI DAIKON E COSTINE',
         'price'=> '0.00',
         'category_id'=> 'ANTIPASTI CUCINA',
         'ingredients'=> 'daikon, costine di maiale',
        ],
        [
         'name'=> 'RAMEN TONKOTSU',
         'price'=> '15.00',
         'category_id'=> 'PIATTI SPECIALI',
         'ingredients'=> 'brodo di osso di maiale\/pollo\/manzo e alghe giapponese, uova, carne stufato, erba cipollina, alghe nori, verdura mista',
        ],
        [
         'name'=> 'PANCETTA CINESE CON IL RISO',
         'price'=> '0.00',
         'category_id'=> 'PRIMI',
         'ingredients'=> 'riso al vapore, pancetta in salsa rossa dolce cinese',
        ],
        [
         'name'=> 'PADELLA CALDA VERZA',
         'price'=> '0.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'verza, peperoni',
        ],
        [
         'name'=> 'PADELLA CALDA CALAMARI',
         'price'=> '0.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'calamari, salsa hot pot(poco piccante),peperoni, cipolla, erba cipollina',
        ],
        [
         'name'=> 'PADELLA CALDA MAIALE',
         'price'=> '0.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'maiale, salsa hot pot(poco piccante),peperoni, cipolla, erba cipollina',
        ],
        [
         'name'=> 'PADELLA CALDA DI GAMBERETTI',
         'price'=> '0.00',
         'category_id'=> 'SECONDI',
         'ingredients'=> 'gamberetti (poco piccante), peperoni, cipolla, erba cipollina',
        ],
        [
         'name'=> 'CHEESCAKE',
         'price'=> '0.00',
         'category_id'=> 'DESSERT',
         'ingredients'=> 'frutta di stagione, base di biscotti, philadelphia, yogurt, latte, uova, zucchero',
        ],
        [
         'name'=> 'CREMA CATALANA',
         'price'=> '0.00',
         'category_id'=> 'DESSERT',
         'ingredients'=> 'uova, latte, gelatina di pesce, zucchero di canna',
        ],
        [
         'name'=> 'CETRIOLO',
         'price'=> '0.00',
         'category_id'=> 'HOSOMAKI',
         'ingredients'=> 'riso, alghe, cetriolo',
        ],
        [
         'name'=> 'FRITTO HOSO MANGO',
         'price'=> '0.00',
         'category_id'=> 'HOSOMAKI FRITTO',
         'ingredients'=> 'riso, alghe, salmone, mango, salsa mango, farina di tempura',
        ],
        [
         'name'=> 'RIO',
         'price'=> '0.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'riso, alghe, tonno, maionese al zafferanno, polvere di tempura',
        ],
        [
         'name'=> 'PATATA DOLCE',
         'price'=> '0.00',
         'category_id'=> 'URAMAKI',
         'ingredients'=> 'patate dolci in tempura salsa teyaki e polvere di tempura',
        ],
        [
         'name'=> 'DOLCE NEVE',
         'price'=> '0.00',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'riso, philadelphia, giuggiole, salsa teyaki, miele',
        ],
        [
         'name'=> 'CHIPS SALMON',
         'price'=> '0.00',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'chips di patatina, salmone',
        ],
        [
         'name'=> 'CHIPS SPICY SALMON',
         'price'=> '0.00',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'chips di patatina, salmone, salsa piccante',
        ],
        [
         'name'=> 'CHIPS TUNA',
         'price'=> '0.00',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'chips di patatina, tonno',
        ],
        [
         'name'=> 'CHIPS SPICY TUNA',
         'price'=> '0.00',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'chips di patatina, tonno, salsa piccante',
        ],
        [
         'name'=> 'GUNKAN SALMONE FLAMBE',
         'price'=> '0.00',
         'category_id'=> 'GUNKAN',
         'ingredients'=> 'salmone scottato, maionese al zafferano e senape',
        ],
        [
         'name'=> 'SALMONE E PURE',
         'price'=> '0.00',
         'category_id'=> 'ANTIPASTI FREDDI',
         'ingredients'=> 'salmone, pure, salsa teyaki e uova di lompo',
        ],
        [
         'name'=> 'BOX 1',
         'price'=> '10.00',
         'category_id'=> 'MENU MIX SUSHI',
         'ingredients'=> 'nigiri mix 8 pz',
        ],
        [
         'name'=> 'BOX 2',
         'price'=> '15.00',
         'category_id'=> 'MENU MIX SUSHI',
         'ingredients'=> '6 nigiri 4 uramaki 6 hosomaki ',
        ],
        [
         'name'=> 'BOX 3',
         'price'=> '20.00',
         'category_id'=> 'MENU MIX SUSHI',
         'ingredients'=> '7 sashimi 4 nigiri 4 uramaki ',
        ],
        [
         'name'=> 'BOX 4',
         'price'=> '30.00',
         'category_id'=> 'MENU MIX SUSHI',
         'ingredients'=> '11 sashimi 6 nigiri 4 uramaki 6 hosomaki ',
        ],
        [
         'name'=> 'involtino di gamberi',
         'price'=> '1.00',
         'category_id'=> 'FRITTURE',
        ],
        [
         'name'=> 'VINO ROSSO MONTEPULCIANO D\'ABRUZZO',
         'price'=> '1.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'FRANZINI',
         'price'=> '1.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'TERRE DI CHIETI PASSERINA',
         'price'=> '1.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'GAMBERETTI IN KATAIFI',
         'price'=> '1.00',
         'category_id'=> 'FRITTURE',
        ],
        [
         'name'=> 'OSTRICA',
         'price'=> '1.00',
         'category_id'=> 'PIATTI SPECIALI',
        ],
        [
         'name'=> 'SPUMANTE DOLCE MONTELVINI',
         'price'=> '1.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> 'TERRE DI CHIETI CHARDONNAY',
         'price'=> '1.00',
         'category_id'=> 'VINO BIANCO',
        ],
        [
         'name'=> '1 PEZZO PANNOCCHIE AL VAPORE',
         'price'=> '5.00',
         'category_id'=> 'PIATTI SPECIALI',
        ],
        [
         'name'=> 'CAPASANTA AL VAPORE CON SPAGHETTI DI SOIA E SALSA',
         'price'=> '5.00',
         'category_id'=> 'PIATTI SPECIALI',
        ]
    ];
       $newproducts=[];
       

    
       foreach ($products as $product) {
        $category_id = 1;
        if($product['category_id'] == 'PRIMI'){
            $category_id = 2;
        } else if($product['category_id'] == 'SECONDI'){
            $category_id = 3;
        }
        else if($product['category_id'] == 'CONTORNI'){
            $category_id = 4;
        }
        else if($product['category_id'] == 'FRITTURE'){
            $category_id = 5;
        }
        else if($product['category_id'] == 'HOSOMAKI'){
            $category_id = 6;
        }
        else if($product['category_id'] == 'HOSOMAKI FRITTO'){
            $category_id = 7;
        }
        else if($product['category_id'] == 'PIATTI SPECIALI'){
            $category_id = 8;
        }
        else if($product['category_id'] == 'GUNKAN'){
            $category_id = 9;
        }
        else if($product['category_id'] == 'ANTIPASTI CUCINA'){
            $category_id = 10;
        }
        else if($product['category_id'] == 'ANTIPASTI FREDDI'){
            $category_id = 11;
        }
        else if($product['category_id'] == 'PIASTRA'){
            $category_id = 12;
        }
        else if($product['category_id'] == 'NIGIRI'){
            $category_id = 13;
        }
        else if($product['category_id'] == 'FLAMBE'){
            $category_id = 14;
        }
        else if($product['category_id'] == 'SASHIMI'){
            $category_id = 15;
        }
        else if($product['category_id'] == 'CARPACCI'){
            $category_id = 16;
        }
        else if($product['category_id'] == 'TEMAKI'){
            $category_id = 17;
        }
        else if($product['category_id'] == 'URAMAKI'){
            $category_id = 18;
        }
        else if($product['category_id'] == 'VAPORIERA'){
            $category_id = 19;
        }
        else if($product['category_id'] == 'MENU MIX SUSHI'){
            $category_id = 20;
        }
        else if($product['category_id'] == 'BEVANDE'){
            $category_id = 21;
        }
        else if($product['category_id'] == 'BIRRE'){
            $category_id = 22;
        }
        else if($product['category_id'] == 'AMARI'){
            $category_id = 23;
        }
        else if($product['category_id'] == 'DESSERT'){
            $category_id = 24;
        }
        else if($product['category_id'] == 'CAFFETTERIA '){
            $category_id = 25;
        }
        else if($product['category_id'] == 'GRAPPE'){
            $category_id = 26;
        }
        else if($product['category_id'] == 'VINO BIANCO'){
            $category_id = 27;
        }
        else if($product['category_id'] == 'VINO ROSE'){
            $category_id = 28;
        }
        else if($product['category_id'] == 'SPINA'){
            $category_id = 29;
        }
        //dump( $product);
        $arrIdTag=[];
        if(isset($product['ingredients'])){
            $ingredients = explode(', ', $product['ingredients']);
            if(count($ingredients) >= 1){
                foreach ($ingredients as $ingredient) {
                    $check = Ingredient::where('name', $ingredient)->exists();
                    //dump( $ingredient);
                    if(!$check){
                        $newTag = new Ingredient();
                        $newTag->name          = strtolower($ingredient);
                        $newTag->price         = 0;
                        $newTag->type          = '[]';
                        $newTag->option        = 0;
                        $newTag->allergens     = '[]';

                        $newTag->save();
                        array_push($arrIdTag, $newTag->id);
                    }else{
                        $ingredient = Ingredient::where('name', $ingredient)->firstOrFail();
                        array_push($arrIdTag, $ingredient->id);            
                    }
                }
            }
        }
        $newproduct=[
            'name'=> $product['name'],
            'price'=> floatval($product['price']) * 100,
            'category_id'=> $category_id,
            'arrIdTag'=> $arrIdTag,
        
        ];
        array_push($newproducts, $newproduct);
        //dump( $newproducts);
    };
    
    
    foreach ($newproducts as $product) {
        $newProduct = Product::create([
            'category_id'   => $product['category_id'],
            'name'          => $product['name'],
            'price'         => $product['price'],
            'visible'       => ($product['price'] > 0) ? 1 : 0,
            'archived'      => ($product['price'] > 0) ? 0 : 1,          
            'slot_plate'    => 1,           
            'type_plate'    => 0,           
            'tag_set'       => 1,           
            'description'    => '',           
            'allergens'    => '[]',           
        ]);
        if(count($product['arrIdTag'])){

            $newProduct->ingredients()->sync($product['arrIdTag'] ?? []);
        }
    }
}
}
