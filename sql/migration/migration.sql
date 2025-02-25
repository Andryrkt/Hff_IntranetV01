----- execution avant migration
alter table devis_soumis_a_validation
add num_migr INT

---- execution apres migration
update demande_intervention
set numero_or=null
where num_migr=4 and numero_or<>'16416070'


UPDATE demande_intervention
SET
    agence_emetteur_id = CASE
        WHEN LEFT(agence_service_emmeteur, 2) = '01' THEN '1'
        WHEN LEFT(agence_service_emmeteur, 2) = '02' THEN '2'
        WHEN LEFT(agence_service_emmeteur, 2) = '20' THEN '3'
        WHEN LEFT(agence_service_emmeteur, 2) = '30' THEN '4'
        WHEN LEFT(agence_service_emmeteur, 2) = '40' THEN '5'
        WHEN LEFT(agence_service_emmeteur, 2) = '50' THEN '6'
        WHEN LEFT(agence_service_emmeteur, 2) = '60' THEN '7'
        WHEN LEFT(agence_service_emmeteur, 2) = '80' THEN '8'
        WHEN LEFT(agence_service_emmeteur, 2) = '90' THEN '9'
        WHEN LEFT(agence_service_emmeteur, 2) = '91' THEN '10'
        WHEN LEFT(agence_service_emmeteur, 2) = '92' THEN '11'
        ELSE '0'
    END
    WHERE num_migr=4;

UPDATE demande_intervention
SET
    service_emetteur_id = CASE
        WHEN RIGHT(agence_service_emmeteur, 3) = 'NEG' THEN '1'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'COM' THEN '2'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'ATE' THEN '3'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'CSP' THEN '4'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'GAR' THEN '5'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'FOR' THEN '6'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'ASS' THEN '7'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'MAN' THEN '8'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'LCD' THEN '9'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'DIR' THEN '10'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'FIN' THEN '11'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'PER' THEN '12'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'INF' THEN '13'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'IMM' THEN '14'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'TRA' THEN '15'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'APP' THEN '16'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'UMP' THEN '17'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'ENG' THEN '19'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'VAN' THEN '20'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'GIR' THEN '21'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'THO' THEN '22'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'TSI' THEN '23'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'LTV' THEN '24'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'LFD' THEN '25'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'LBV' THEN '26'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'MAH' THEN '27'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'NOS' THEN '28'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'TUL' THEN '29'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'AMB' THEN '30'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'FLE' THEN '31'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'TSD' THEN '32'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'VAT' THEN '33'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'BLK' THEN '34'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'ENG' THEN '35'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'MAS' THEN '36'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'MAP' THEN '37'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'ADM' THEN '38'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'APP' THEN '39'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'LEV' THEN '40'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'LR6' THEN '41'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'LST' THEN '42'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'LCJ' THEN '43'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'TSI' THEN '44'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'SLR' THEN '45'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'LGR' THEN '46'
        WHEN RIGHT(agence_service_emmeteur, 3) = 'LSC' THEN '46'
        ELSE '0'
    END
    WHERE num_migr=4;



    UPDATE demande_intervention
SET mail_demandeur = CASE 
    WHEN utilisateur_demandeur = 'aina' THEN  'aina.rajaonarivelo@hff.mg'
    WHEN utilisateur_demandeur = 'ambroise' THEN  'ambroise.rakotoarisoa@hff.mg'
    WHEN utilisateur_demandeur = 'cathy' THEN  'cathy.andrianarimanana@hff.mg'
    WHEN utilisateur_demandeur = 'davida' THEN  'davida.ramahatantsoa@hff.mg'
    WHEN utilisateur_demandeur = 'domoina' THEN  'domoina.rakotohasy@hff.mg'
    WHEN utilisateur_demandeur = 'Donat' THEN  'donat.rabarone@hff.mg'
    WHEN utilisateur_demandeur = 'estella' THEN  'estella.razafiarisoa@hff.mg'
    WHEN utilisateur_demandeur = 'faneva' THEN  'faneva.rabarijaona@hff.mg'
    WHEN utilisateur_demandeur = 'fetra' THEN  'fetra.rakotomalalatiana@hff.mg'
    WHEN utilisateur_demandeur = 'fidinirina' THEN  'fidinirina.rasamimanana@hff.mg'
    WHEN utilisateur_demandeur = 'finaritra' THEN  'finaritra.rakotoarimanana@hff.mg'
    WHEN utilisateur_demandeur = 'gaelle.lecohu' THEN  'gaelle.lecohu@hff.mg'
    WHEN utilisateur_demandeur = 'gillot' THEN  'gillot.emile@hff.mg'
    WHEN utilisateur_demandeur = 'Hasina' THEN  'hasina.raharinasinavalona@hff.mg'
    WHEN utilisateur_demandeur = 'helimino' THEN  'helimino.andriamihaja@hff.mg'
    WHEN utilisateur_demandeur = 'hoby' THEN  'hoby.rasoazanamiarana@hff.mg'
    WHEN utilisateur_demandeur = 'HOLY' THEN  'holy.ranaivoson@hff.mg'
    WHEN utilisateur_demandeur = 'maharo' THEN  'maharo.ratsimandresy@hff.mg'
    WHEN utilisateur_demandeur = 'malala' THEN  'malala.rajonson@hff.mg'
    WHEN utilisateur_demandeur = 'mamitiana' THEN  'mamitiana.ravaoarisoa@hff.mg'
    WHEN utilisateur_demandeur = 'mampionona' THEN  'prisca.soloniaina@hff.mg'
    WHEN utilisateur_demandeur = 'marie' THEN  'marie.muhimana@hff.mg'
    WHEN utilisateur_demandeur = 'martin' THEN  'martin.randrianantoanina@hff.mg'
    WHEN utilisateur_demandeur = 'mendrika.f' THEN  'mendrika.randriarimalala@hff.mg'
    WHEN utilisateur_demandeur = 'Mihanta' THEN  'mihanta.rasoloarison@hff.mg'
    WHEN utilisateur_demandeur = 'Miora.energie' THEN  'miora.razafimahazo@hff.mg'
    WHEN utilisateur_demandeur = 'naina' THEN  'niaina.randrianaivoravelona@hff.mg'
    WHEN utilisateur_demandeur = 'nancy' THEN  'nancy.ratovoarisolo@hff.mg'
    WHEN utilisateur_demandeur = 'nomentsoa' THEN  'diamondra.razafiniary@hff.mg'
    WHEN utilisateur_demandeur = 'Norolalao' THEN  'norolalao.harimanana@hff.mg'
    WHEN utilisateur_demandeur = 'oliva' THEN  'oliva.ramaroson@hff.mg'
    WHEN utilisateur_demandeur = 'omega' THEN  'omega.razanadrasoa@hff.mg'
    WHEN utilisateur_demandeur = 'onitiana' THEN  'onitiana.ranaivoarison@hff.mg'
    WHEN utilisateur_demandeur = 'ony.rafalimanana' THEN  'ony.rafalimanana@hff.mg'
    WHEN utilisateur_demandeur = 'paul.marcusse' THEN  'paul.marcusse@hff.mg'
    WHEN utilisateur_demandeur = 'Prisca' THEN  'prisca.michea@hff.mg'
    WHEN utilisateur_demandeur = 'r.alisoa' THEN  'alisoa.rakotoarivony@hff.mg'
    WHEN utilisateur_demandeur = 'rachel' THEN  'rachel.ralalarinarivo@hff.mg'
    WHEN utilisateur_demandeur = 'radonirina' THEN  'radonirina.andriantsimba@hff.mg'
    WHEN utilisateur_demandeur = 'rajohnson' THEN  'fenohery.rajohnson@hff.mg'
    WHEN utilisateur_demandeur = 'rojo' THEN  'rojo.ramamonjy@hff.mg'
    WHEN utilisateur_demandeur = 'roussel' THEN  'antoine.roussel@hff.mg'
    WHEN utilisateur_demandeur = 'setra' THEN  'setra.razanamparany@hff.mg'
    WHEN utilisateur_demandeur = 'Tahiantsoa' THEN  'tahiantsoa.rafaliarivony@hff.mg'
    WHEN utilisateur_demandeur = 'tiana' THEN  'tiana.andrianarivelo@hff.mg'
    WHEN utilisateur_demandeur = 'tsiry' THEN  'tsirivao.radison@hff.mg'
    WHEN utilisateur_demandeur = 'vania' THEN  'vania.rakotomanga@hff.mg'
    WHEN utilisateur_demandeur = 'Voahangy' THEN  'seheno.raholiarimanga@hff.mg'
    WHEN utilisateur_demandeur = 'zoary' THEN  'zoary.andriamanantena@hff.mg'
    ELSE '-'
END
WHERE num_migr=4

