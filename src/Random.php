<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

class Random
{
    private static array $words = [
        "semangat","perhatian","kegairahan","kegiatan","zealot","orang","yg","bersemangat","dlm","suatu","usaha", "fanatik","peng","zealous","rajin","bersemangat","zenith","puncak","ketenaran","zephyr", "angin","barat",
        "angin","sepoi","sepoi","zest","minat","besar","gairah","animo","zest","bumbu","rasa","enak","perangsang", "zip","bunyi","desing","semangat","zipper","retsluiting","zip","fastener","retsluiting", "yatch","kapal",
        "ringan","utk","bunga","kapal","mewah","utk","pesiar","yak","lembu","berbulu","panjang","di","Asia", "Tengah","yam","ubi","yap","mendengking","menyalak","omong","kosong","yard","kayu","utk","menggantungkan",
        "yearning","rindu","sekali","yeast","ragi","yellow","fever","sakit","kuning","yelp","menyalak","yeoman", "menolong","org","dlm","kesusahan","yew","pohon","cemara","berdaun","hijau","muda","yield","menghasilkan",
        "kayu","tengkuk","pikulan","bag","dada","pd","gaun","yoke","of","oxen","sepasang","lembu","yoke","pass", "udik","yolk","kuning","telur","yon","di","sana","yonder","di","sana","yore","berkenaan","dgn","jaman",
        "hari","Natal","wroth","marah","gusar","wrought","iron","besi","tempa","wroughtup","state","kead","gelisah", "remedies","obat","yg","dijual","oleh","tukang","obat","quadrangle","benda","berbentuk","segi","bj",
        "alat","pengukur","sudut","quadrilateral","benda","berbentuk","segi","quadruped","hewan","berkaki", "quagmire","rawa","paya","quail","takut","hilang","semangat","quaint","menarik","menyenangkan","krn",
        "gereja","Kristen","yg","mengadakan","kebaktian","tan","quaker","pa","seorang","pendeta","qualification", "qualm","ragu","ragu","sangsi","sesal","mual","quandary","kead","ragu","ragu","bimbang","dilema","quarrel",
        "buruan","perburuan","mangsa","quart","galon","ltr","quartette","penyanyi","quartz","batu","akik","permata", "sajak","baris","quaver","bunyi","bergetar","gemetar","titi","nada","quay","dermaga","queasy","memualkan",
        "quarter","penjuru","daerah","kota","ampun","pon","pondokan","markas","quarterday","hari","pembayaran", "api","harapan","menghilangkan","haus","querulous","suka","mengeluh","mengomel","query","menanyakan",
        "berselisih","ttg","hal","yg","kecil","quicklime","kapur","mentah","quicksand","pasir","hanyut", "diam","masif","quietude","kesunyian","ketenangan","quieten","menenangkan","quill","bulu","burung","pen",
        "quinine","pil","kina","quintessence","contoh","yg","sempurna","quintette","penyanyi","org","quip", "menggetarkan","tempat","anak","panah","qui","vive","waspada","berjaga","jaga","quixotic","pemurah",
        "suatu","permainan","quota","jatah","quotient","hasil","bagi","quoth","berkata","quote","mengutip", "ternak","kowtow","membungkuk","sangat","menghormati","kosher","toko","makanan","bangsa","Yahudi",
        "kecil","knick","knack","barang","penghias","kecil","yg","tdk","penting","knickers","celana","dalam", "lutut","knead","memukul","adonan","meremas","memijit","mengurut","knapsack","ransel","knave","penjahat",
        "ketrampilan","ketangkasan","seni","kith","and","kin","teman","sanak","saudara","kite","burung","elang", "alat","perkakas","kipper","ikan","kecil","yg","diasap","diasin","knob","tombol","menonjol","bungkal",
        "elang","kecil","keg","tong","kecil","kennel","kandang","anjing","kerb","tepi","jalan","trotoar","kerchief", "kead","canggung","serba","salah","kettledrum","gendang","kidney","ginjal","kidnap","menculik","kid","anak",
        "pembakaran","kapur","batubara","kin","next","of","saudara","dekat","kine","sapi","lembu","kiosk","kios", "kinswoman","kerabat","laki","perempuan","kingfisher","burung","kecil","pemakan","ikan","di","sungai",
        "menyalakan","api","misalnya","ranting","kering","kangaroo","kangguru","kapok","kapuk","keel","lunas", "luar","jute","goni","juvenile","remaja","juxtapose","menempatkan","berdampingan","juxtaposition","posisi",
        "jurisprudence","ilmu","ttg","hukum","jurisdiction","hal","mengadili","daerah","kekuasaan","kekuasaan", "pohon","cemara","yg","buahnya","utk","minyak","juncture","hubungan","junction","at","this","kead","spt",
        "hakim","kehakiman","judicial","berkenaan","dgn","pengadilan","judicious","bijaksana","jumble","bercampur", "junketing","pesta","makan","juggernaut","keyakinan","mengorbankan","diri","sendiri","jugular","veins",
        "menunjukkan","kemenangan","kegembiraan","jubilation","sorak","kegirangan","jubilee","ultah","ke","silver", "penuh","kegembiraan","jowl","rahang","bag","bawah","wajah","cheek","by","jowl","sangat","rapat","cheek",
        "berkelahi","dgn","tombak","sambil","naik","kuda","jot","down","mencatat","dgn","cepat","jot","jml","yg", "jostle","mendesak","mendorong","jolt","berguncang","mengguncang","jolly","gembira","jollity","kead",
        "joinery","pekerjaan","tukang","kayu","jocund","gembira","jocose","bergurau","lucu","jocular","lucu", "jag","ujung","karang","yg","tajam","jade","batu","permata","hijau","jackal","serigala","jacket","kulit",
        "pesuruh","jangle","bunyi","berdencing","jargon","bhs","yg","dipakai","oleh","kelompok","tertentu", "cerah","yg","ribut","bunyinya","jeer","mencemooh","mengejek","menertawakan","jemmy","linggis","jelly",
        "ketat","dr","kulit","jersey","baju","kaos","jetty","dermaga","pangkalan","jib","layar","kecil", "itinerary","rencana","perjalanan","IOU","surat","hutang","isthmus","genting","tanah","itch","gatal",
        "ire","kemarahan","irascible","cepat","marah","iris","selaput","pelangi","lumba","yg","daunnya","panjang", "inveigh","against","menyerang","dgn","kata","inveigle","mmebujuk","spy","berbuat","sesuatu","inure",
        "intrude","masuk","tanpa","diundang","mengganggu","intrusion","gangguan","masuk","tanpa","diundang", "inundate","membanjiri","intimidate","menakuti","mengancam","menggertak","intestine","usus","interplay",
        "surat","wasiat","wkt","meninggal","interchange","tukar","menukar","intercept","menghadang","mencegat", "jujur","lurus","kead","lengkap","komplit","intend","bermaksud","bertujuan","berniat","inter","mengubur",
        "dr","index","indigenous","pribumi","indigo","biru","tua","nila","infantry","pasukan","jalan","kaki", "burung","bangkai","burung","nasar","vulnerable","mudah","rusak","tdk","kebal","vulgar","tdkk","sopan",
        "tdk","berlaku","voile","kain","voal","tipis","utk","gaun","viva","voce","scr","lisan","viz","namely", "wadi","sungai","kering","di","Mesir","wadi","sultry","pengap","panas","tdk","berangin","superb","hebat",
        "pantai","supine","terlentang","malas","pap","bubur","bayi","papacy","pemerintahan","Paus","papal", "sahabat","pallor","muka","pucat","pallid","pucat","kelihatan","sakit","palisade","pagar","dr","batang",
        "abandon","putus","asa","batal","abase","menghina","menurunkan","martabat","abasement","penghinaan", "penjagalan","hewan","abbess","kepala","asrama","biarawati","abbott","kepala","biarawan","biara","abbey",
        "isinya","abduct","penculikan","abet","aid","membantu","menghasut","abeyance","dlm","kead","non","aktif", "abound","berlimpah","limpah","abide","rumah","tinggal","abode","rumah","tinggal","aboard","dlm",
        "umum","akan","melepaskan","jabatan","abscess","bisul","bettle","kumbang","befall","menimpa","befit", "halus","amfibi","becalmed","kapal","yg","terhenti","krn","tdk","ada","angin","beckon","memanggil",
        "bertingkah","aneh","bedlam","huru","hara","rumah","sakit","jiwa","bee","lebah;make","a","line","for", "lembu","beige","warna","batu","pasir","belabour","menghantam","belated","terlambat","beleaguer",
        "menara","lonceng","belie","memberi","kesan","keliru","tdk","menepati","janji","bellicose","suka", "berteriak","keras","bellows","alat","penghembus","udara","ke","api","orgel","belly","isi","perut",
        "igneous","bukit","yg","terbentuk","oleh","panas","gunung","berapi","ignite","menyalakan","ignoble", "menancapkan","imbecile","dungu","imbibe","minum","meneguk","mencamkan","menyerap","imbue","penuh",
        "immemorial","dulu","kala","shg","terlupakan","immerse","membenamkan","imp","anak","setan","nakal", "meneruskan","rahasia","impasse","jln","buntu","impassioned","penuh","semangat","odour","berbau","ode",
        "achre","warna","coklat","kekuningan","octagon","al","segi","occident","negara","Barat","obviate", "welter","berkubang","kekacauan","wend","pergi","ke","wench","perempuan","muda","wer","biri","jantan",
        "susu","dibuat","keju","vale","lembah","upbraid","memarahi","upbringing","mengasuh","upstream","ke", "hantu","spool","kumparan","spoor","jejak","binatang","spore","spora","spouse","suami","istri","sprat",
        "tinggi","runcing","pada","gereja","prosody","pengetahuan","ttg","persajakan","prosaic","biasa", "perkamusan","lichen","semacam","lumut","liar","pembohong","libation","persembahan","anggur","kpd",
        "hewan","laut","sesuatu","yg","besar","lever","pengungkit","leverage","daya","pengungkit","levity", "batu","kawi","mange","kurap","mangy","berkudis","manger","palungan","mangle","alat","pemeras","cucian",
        "manila","rami","manifest","nyata","daftar","muatan","kapal","wujud","manifesto","pernyataan","prinsip", "tanah","milik","bangsawan","manse","rumah","pendeta","grj","Presbitaria","mansion","rumah","besar","indah",
        "manure","pupuk","baja","manuscipt","naskah","map","out","merancang","menyusun","mengatur","maple","kayu", "berlalunya","peristiwa","marchioness","istri","janda","bangsawan","inggris","mare","kuda","betina","mares",
        "dgn","laut","mariner","pelaut","marionette","boneka","yg","digerakkan","dgn","tali","kecil","marital", "merah","hati","ayam","maroon","kembang","api","utk","isyarat","maroon","meninggalkan","org","di","pulau",
        "gelar","bangsawan","di","bawah","pangeran","marrow","sumsum","vegetable","labu","marsh","rawa","marshal", "martin","burung","layang","martinet","org","yg","berpegang","teguh","pd","tata","tertib","martyrdom",
        "pijit","mast","tiang","masticate","mengunyah","mat","keset","tikar","kusam","matted","rambut","kusut", "matriarch","wanita","yg","mjd","kepala","keluarga","suku","matricide","pembunuhan","ibu","kandung",
    ];

    private static array $userAgents = [
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1.2 Safari/605.1.15",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0",
        "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:61.0) Gecko/20100101 Firefox/61.0",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/17.17134",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.75 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_5) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1.1 Safari/605.1.15",
        "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (X11; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko",
        "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1.2 Safari/605.1.15",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.75 Safari/537.36",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:61.0) Gecko/20100101 Firefox/61.0",
        "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.75 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (X11; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1 Safari/605.1.15",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/64.0.3282.119 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0",
        "Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:62.0) Gecko/20100101 Firefox/62.0",
        "Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0",
        "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36 Edge/16.16299",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_3) AppleWebKit/604.5.6 (KHTML, like Gecko) Version/11.0.3 Safari/604.5.6",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/67.0.3396.99 Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (iPad; CPU OS 11_4_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.0 Mobile/15E148 Safari/604.1",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.11; rv:61.0) Gecko/20100101 Firefox/61.0",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.75 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:61.0) Gecko/20100101 Firefox/61.0",
        "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:56.0) Gecko/20100101 Firefox/56.0",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:61.0) Gecko/20100101 Firefox/61.0",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; rv:52.0) Gecko/20100101 Firefox/52.0",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1.2 Safari/605.1.15",
        "Mozilla/5.0 (Windows NT 6.1; rv:61.0) Gecko/20100101 Firefox/61.0",
        "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/66.0.3359.181 Chrome/66.0.3359.181 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:60.0) Gecko/20100101 Firefox/60.0",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.84 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Safari/605.1.15",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.181 Safari/537.36",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/68.0.3440.75 Chrome/68.0.3440.75 Safari/537.36",
        "Mozilla/5.0 (X11; Fedora; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36",
        "Mozilla/5.0 (Windows NT 10.0; WOW64; rv:52.0) Gecko/20100101 Firefox/52.0",
        "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Safari/537.36",
        "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:55.0) Gecko/20100101 Firefox/55.0",
        "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3493.0 Safari/537.36",
    ];

    /**
     * Returns a random token. Using only selected letters that don't go below the baseline (like, y, j, etc.) to make the output prettier.
     * Not using i, l, 0, o to avoid confusion.
     *
     * @param int $length
     * @param bool $uppercase
     * @return string
     */
    public static function getToken(int $length = 16, bool $uppercase = false): string
    {
        $characters = "abcdefhkmnrstuvwxz123456789123456789";

        $token = "";
        while (strlen($token) < $length) {
            $token .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $uppercase ? strtoupper($token) : $token;
    }

    public static function getHtml($minParagraphs = 3, $maxParagraphs = 5): string
    {
        $paragraphs = rand($minParagraphs, $maxParagraphs);

        $i = 1;
        $output = "";
        $hasImage = false;

        while ($i <= $paragraphs) {
            if (!rand(0, 5)) {
                $output .= "<h4>" . self::getWordList(4, 6, " ") . "</h4>";
                $i++;
            }

            $output .= "<p style='padding:3px 0;'>" . self::getParagraph(5, 10) . "</p>";
            $i++;

            if (!rand(0, 4)) {
                $output .= self::getList();
                $i++;
            }

            if (!rand(0, 4)) {
                $output .= self::getTable();
                $i++;
            }

            if (($i > $paragraphs && !$hasImage) || !rand(0, 5)) {
                $output .=
                    "<p style='padding:3px 0;text-align:center;'>".
                    "<img src='https://picsum.photos/300/200' alt='' />".
                    "</p>".
                    "<p style='padding:3px 0;'>" . self::getParagraph(5, 10) . "</p>";
                $i++;
                $hasImage = true;
            }
        }

        return "<html lang='en'><body>".
            "<p style='padding:3px 0;'>" . self::getWord(true) . " " . self::getWord(true) . ",</p>".
            $output.
            "<p style='padding:3px 0;'>" . self::getWord(true) . ",<br />" . self::getWord(true) . "</p>".
            "</body></html>";
    }

    public static function getWord($firstLetterCapital = false): string
    {
        $word = self::$words[rand(0, count(self::$words) - 1)];
        if ($firstLetterCapital) {
            $word = strtoupper($word[0]) . substr($word, 1);
        }

        return $word;
    }

    public static function getUserAgent(): string
    {
        return self::$userAgents[rand(0, count(self::$userAgents) - 1)];
    }

    public static function getIp(): string
    {
        return rand(1, 255) . "." . rand(1, 255) . "." . rand(1, 255) . "." . rand(1, 255);
    }

    public static function getWordList($minWordCount = 5, $maxWordCount = 5, $delimiter = ", ", $firstCapitalLetter = true): string
    {
        $s = "";
        for ($i = 0; $i < rand($minWordCount, $maxWordCount); $i++) {
            $s .= self::getWord($firstCapitalLetter) . $delimiter;
        }

        return substr($s, 0, strlen($delimiter) * -1);
    }

    public static function getName($minWordCount = 2, $maxWordCount = 2): string
    {
        $s = "";
        for ($i = 0; $i < rand($minWordCount, $maxWordCount); $i++) {
            $s .= self::getWord(true) . " ";
        }

        return trim($s);
    }

    public static function getPhoneNumber($firstPartLength = 3, $secondPartLength = 4, $includeDialingCode = true): string
    {
        $s = $includeDialingCode ? "(" . self::getNumber(3, 3, true) . ") " : "";
        $s .= self::getNumber($firstPartLength, $firstPartLength, true);
        if ($secondPartLength) {
            $s .= "-" . self::getNumber($secondPartLength, $secondPartLength, true);
        }

        return $s;
    }

    public static function getParagraph($minSentenceCount = 1, $maxSentenceCount = 3): string
    {
        $tags = array(
            array("<span style='color:%s'>", "</span>"),
            array("<strong>", "</strong>"),
            array("<em>", "</em>"),
            array("<u>", "</u>"),
        );

        $colors = array("#AF265F", "#278911", "#AA4225");

        $s = "";
        for ($i = 0; $i < rand($minSentenceCount, $maxSentenceCount); $i++){
            for ($j = 0; $j < rand(5, 20); $j++) {
                if (rand(0, 10) == 10) {
                    $c = rand(1, 10);
                    $tag = $tags[rand(0, count($tags) - 1)];
                    $word = "";

                    for ($k = 1; $k <= $c; $k++) {
                        $word .= self::getWord($j == 0) . " ";
                    }

                    $word = (strpos($tag[0], "color") ? sprintf($tag[0], $colors[rand(0, count($colors) - 1)]) : $tag[0]) .
                        $word . $tag[1];

                } else {
                    $word = self::getWord($j == 0);
                }

                $s .= $word . " ";
            }
            $s = substr($s, 0, -1) . ". ";
        }

        return trim($s);
    }

    public static function getList($minItems = 5, $maxItems = 10): string
    {
        $s = "";

        for ($i = 0; $i < rand($minItems, $maxItems); $i++) {
            $line = "";

            for ($j = 0; $j < rand(3, 6); $j++) {
                $line .= self::getWord($j == 0) . " ";
            }

            $s .= "<li>" . trim($line) . "</li>";
        }

        return "<ul>$s</ul>";
    }

    public static function getTable($minRows = 3, $maxRows = 6): string
    {
        $backgrounds = array("#601B08", "#023D77", "#1B5E05", "#4E0A96");
        $bg = $backgrounds[rand(0, count($backgrounds) - 1)];

        $cols = rand(3, 6);
        $s = "";

        for ($i = 0; $i < $cols; $i++) {
            $s .= "<th style='padding:5px;border:1px solid $bg;background:$bg;color:#fff;'>" . self::getWord(true) . "</th>";
        }

        $s = "<tr>$s</tr>";

        for ($i = 0; $i < rand($minRows, $maxRows); $i++) {
            $s .= "<tr>";

            for ($j = 0; $j < $cols; $j++) {
                $line = "";

                for ($k = 0; $k < rand(2, 5); $k++) {
                    $line .= self::getWord($k == 0) . " ";
                }
                $s .= "<td style='padding:5px;border:1px solid $bg;'>" . trim($line) . "</td>";
            }

            $s .= "</tr>";
        }

        return "<table style='width:95%;border-collapse:collapse;margin:5px auto;'>$s</table>";
    }

    public static function getDomainExtension(): string
    {
        $list = ["com", "org", "eu", "it", "de", "co.uk", "cn", "jp", "sg", "us", "asia"];
        return $list[rand(0, count($list) - 1)];
    }

    public static function getEmail(): string
    {
        return self::getWord() . (rand(0, 1) ? "." . self::getWord() : "") . "@" . self::getWord() . "." . self::getDomainExtension();
    }

    public static function getDomainName(): string
    {
        return self::getWord() . "." . self::getDomainExtension();
    }

    public static function getUrl($includePath = true): string
    {
        $path = "";

        if ($includePath) {
            for ($i = 0; $i < rand(1, 4); $i++) {
                $path .= "/" . self::getWord();
            }
        }

        return "https://" . self::getDomainName() . $path;
    }

    public static function getCountryCode(): string
    {
        $list = array_keys(Countries::getAllCountries());
        return $list[rand(0, count($list) - 1)];
    }

    public static function getNumber($minLength = 5, $maxLength = 5, $includeZero = false): string
    {
        $characters = ($includeZero ? "0" : "") . "123456789";

        $s = "";
        for ($i = 0; $i < rand($minLength, $maxLength); $i++) {
            $s .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $s;
    }

    public static function getColor(): string
    {
        $list = array("blue", "red", "green", "black", "white", "yellow", "pink", "orange", "silver", "gray");
        $s = $list[rand(0, count($list) - 1)];

        return strtoupper($s[0]) . substr($s, 1);
    }

    public static function getString($minLength = 10, $maxLength = 40, $useSpaces = true, $upperCase = false): string
    {
        $characters = $upperCase ? "QWERTYUIOPASDFGHJKLZXCVBNM" : "qwertyuiopasdfghjklzxcvbnm";

        if ($useSpaces) {
            $characters .= "   ";
        }

        $s = "";
        for ($i = 0; $i < rand($minLength, $maxLength); $i++) {
            $s .= $characters[rand(0, strlen($characters) - 1)];
        }

        return trim($s);
    }

    public static function getPassword($minLength = 10, $maxLength = 40): string
    {
        // these characters should be safe for database passwords as well
        $characters = "QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm01234567890_.,#+()-*&![]%^";

        $result = "";
        for ($i = 0; $i < rand($minLength, $maxLength); $i++) {
            $result .= $characters[rand(0, strlen($characters) - 1)];
        }

        return trim($result);
    }
}
