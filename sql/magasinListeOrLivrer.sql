select
    seor_numor,
    slor_nolign,
    slor_constp,
    slor_refp,
    CASE
        WHEN slor_typlig = 'P' THEN (
            slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
        )
        WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
    END as qtedem,
    slor_qteres as qteALivrer
from
    sav_lor
    inner join sav_eor on seor_soc = slor_soc
    and seor_succ = slor_succ
    and seor_numor = slor_numor
where
    slor_soc = 'HF'
    and CASE
        WHEN slor_typlig = 'P' THEN (
            slor_qterel + slor_qterea + slor_qteres + slor_qtewait - slor_qrec
        )
        WHEN slor_typlig IN ('F', 'M', 'U', 'C') THEN slor_qterea
    END = slor_qteres
    and slor_qteres <> 0
    and slor_typlig = 'P'
    and slor_constp not like 'Z%'
    and slor_constp not in('LUB')
    and slor_succ = '01'
    and seor_serv = 'SAV'
order by seor_numor asc, slor_nolign asc