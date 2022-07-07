<?php
/**
 * Programowanie funkcyjne dla tablic PHP.
 * Wersja: 1.5.0
 * Ostatnia zmiana: 2022-07-07
 */
class FuncArr
{
    private $m_rgArr;        
    private $m_bOpen = true;
    
    public function __construct(array $a_rgArr, $a_bOpen = true)
    {
        $this->m_rgArr = &$a_rgArr;
        $this->m_bOpen = $a_bOpen;
    }
    
    /**
     * Finds a value observing predicate. When no value is found, returns NULL.
     */
    public function find(callable $a_cbPred, $a_bWithKey = false)
    {
        foreach ($this->m_rgArr as $mxKey => $mxItem)
        {
            if ($a_cbPred($mxItem))
            {
                if ($a_bWithKey)
                {
                    $mxRes = [$mxKey, $mxItem];
                }
                else
                {
                    $mxRes = $mxItem;
                }
                
                break;
            }
        }
        
        return $mxRes;
    } 

    /**
     * Finds a value satisfying predicate. When no value is found, returns given fallback value.
     */
    public function findOr(callable $a_cbPred, $a_bWithKey = false, $a_mxFallbackValue)
    {
        $bFound = false;

        foreach ($this->m_rgArr as $mxKey => $mxItem)
        {
            if ($a_cbPred($mxItem))
            {
                if ($a_bWithKey)
                {
                    $mxRes = [$mxKey, $mxItem];
                }
                else
                {
                    $mxRes = $mxItem;
                }
                
                $bFound = true;
                break;
            }
        }

        return $bFound ? $mxRes : $a_mxFallbackValue;        
    } 
        
    public function exists($a_mxItemSel)
    {
        $bRes = false;
        
        if (is_callable($a_mxItemSel))
        {
            foreach ($this->m_rgArr as $mxItem)
            {
                if ($a_mxItemSel($mxItem))
                {
                    $bRes = true;
                    break;
                }
            }           
        }
        else 
        {
            $bRes = in_array($a_mxItemSel, $this->m_rgArr);
        }
                
        return $bRes;
    }      
    
    /**
     * Zwraca obiekt z tablica zawierajaca tylko klucze podane w parametrze.
     * @param mixed $a_mxKeys
     * @return type
     */
    public function pick($a_mxKeys)
    {
        $rgRawKeys = $this->rawArr($a_mxKeys);        
        return fp(array_intersect_key($this->m_rgArr, array_flip($rgRawKeys)));
    }
    
    /**
     * Filtruje tablice - zwraca obiekt z tablica zawierajaca tylko wartosci spelniajaca podany
     * predykat.
     */
    public function filter(callable $a_cbPred, $a_bReindex = false)
    {                
        $rgFiltered = array_filter($this->m_rgArr, $a_cbPred);
        $rgFinal = $a_bReindex ? array_values($rgFiltered) : $rgFiltered;        
        return fp($rgFinal);
    }
    
    /**
     * Zwraca obiekt z tablica zawierajaca tylko klucze podane w parametrze i uporzadkowane tak jak
     * w tablicy w parametrze.
     * @param mixed $a_mxKeys
     * @return type
     */
    public function arrange($a_mxKeys)
    {
        $rgRawKeys = $this->rawArr($a_mxKeys);        
        $rgArr = [];
        
        foreach ($rgRawKeys as $mxKey)
        {
            $rgArr[$mxKey] = $this->m_rgArr[$mxKey];
        }
        
        return fp($rgArr);
    }
    
    /**
     * Usuwa wartosc z tablicy w obiekcie.
     */
    public function rem($a_mxVal)
    {
        $rgArr = $this->m_rgArr;
        
        foreach ($rgArr as $mxKey => $mxVal)
        {
            if ($mxVal == $a_mxVal)
            {
                unset($rgArr[$mxKey]);
                break;
            }
        }
        
        return fp($rgArr);
    }
    
    public function remKey($a_mxKey)
    {
        // TODO: uzupelnic
    }
    
    /**
     * Sprawdza, czy dowolna wartosc w tablicy spelnia zadany predykat lub jest rowna zadanej 
     * wartosci.
     * @param callable $a_cbPred
     * @return boolean
     */
    public function any($a_mxTest)
    {
        $bRes = false;
        
        foreach ($this->m_rgArr as $mxItem)
        {
            if (is_callable($a_mxTest) && $a_mxTest($mxItem) || $a_mxTest == $mxItem)
            {
                $bRes = true;
                break;
            }
        }
        
        return $bRes;
    }
    
    /**
     * Mapowanie wartosci. Klucze tablicy pozostaja niezmienione.
     * @param callable $a_cbFunc
     * @return type
     */
    public function map(callable $a_cbFunc)
    {        
        return fp(array_map($a_cbFunc, $this->m_rgArr));
    }
    
    /**
     * Wywoluje podana funkcje dla kazdego elementu tablicy.
     * @param callable $a_cbFunc
     * @return $this
     */
    public function forEach(callable $a_cbFunc)
    {
        foreach ($this->m_rgArr as $mxItem)
        {
            call_user_func($a_cbFunc, $mxItem);
        }
        
        return $this;
    }

    /**
     * Zwraca początkowy fragment tablicy od 1-go elementu do 1-go elementu nie spełniającego podanego predykatu włącznie z tym
     * elementem.
     */
    public function takeWhileIncl(callable $a_cbPred) {
        $nIdx = 0;

        foreach ($this->m_rgArr as $mxKey => $mxItem) {
            if (!$a_cbPred($mxItem)) {
                break;
            }
            else {
                $nIdx++;
            }            
        }

        return fp(array_slice($this->m_rgArr, 0, $nIdx + 1));
    }

    /**
     * Zwraca końcowy fragment tablicy od 1-go elementu nie spełniającego podanego predykatu.
     */
    public function omitWhile(callable $a_cbPred) {
        $nIdx = 0;

        foreach ($this->m_rgArr as $mxKey => $mxItem) {
            if (!$a_cbPred($mxItem)) {
                break;
            }
            else {
                $nIdx++;
            }            
        }

        return fp(array_slice($this->m_rgArr, $nIdx));
    }
    
    /**
     * Mapuje zmieniajac takze klucze. Funkcja mapujaca powinna zwracac 2-elementowe tablice, z 
     * ktorych 1-sza wartosc jest nowym kluczem a 2-ga wartoscia
     * @param callable $a_cbFunc
     */
    public function mapAssoc(callable $a_cbFunc)
    {        
        $rgTupleArr = array_map($a_cbFunc, $this->m_rgArr);                
        $rgKeys = array_map(function($rgTuple) { return $rgTuple[0]; }, $rgTupleArr);
        $rgValues = array_map(function($rgTuple) { return $rgTuple[1]; }, $rgTupleArr);                        
        return fp(array_combine($rgKeys, $rgValues));
    }
    
    /**
     * Mapuje zmieniajac takze klucze. Parametrem funkcji mapujacej jest dwuelementowa tablica 
     * zawierajaca klucz z tablicy mapowanej jako 1-szy element, a wartosc jako drugi. 
     * @param callable $a_cbFunc
     */
    public function mapAssocFull(callable $a_cbFunc)
    {
        // pary - tablica tablic dwuelementowych postaci (klucz, wartosc) z kluczem i wartoscia z 
        // pierwotnej tablicy ($this->m_rgArr)
        $rgPairs = [];
        
        foreach ($this->m_rgArr as $mxKey => $mxVal)
        {
            $rgPairs[] = [$mxKey, $mxVal];
        }
        
        $rgTupleArr = array_map($a_cbFunc, $rgPairs);                        
        $rgKeys = array_map(function($rgTuple) { return $rgTuple[0]; }, $rgTupleArr);        
        $rgValues = array_map(function($rgTuple) { return $rgTuple[1]; }, $rgTupleArr);                        
        return fp(array_combine($rgKeys, $rgValues));
    }
    
    /**
     * Mapuje tylko klucze.
     * @param callable $a_cbFunc
     */
    public function mapKeys(callable $a_cbFunc)
    {
        $rgOldKeys = array_keys($this->m_rgArr);
        $rgNewKeys = array_map($a_cbFunc, $rgOldKeys);
        return fp(array_combine($rgNewKeys, array_values($this->m_rgArr)));
    }
    
    public function flatMap(callable $a_cbFunc)
    {
        $rgMapped = array_map($a_cbFunc, $this->m_rgArr);                
        
        return fp(array_reduce($rgMapped, function($a_rgAcc, $a_mxItem) {            
            if (!is_array($a_mxItem)) 
            {
                $a_mxItem = array($a_mxItem);                
            }
            
            return array_merge($a_rgAcc, $a_mxItem);
        }, array()));
    }

    /**
     * Usuwa powtórzone wartości
     */
    public function unique(?bool $a_bReindex = false)
    {
        $rgNewArr1 = array_unique($this->m_rgArr);
        $rgNewArr = $a_bReindex ? array_values($rgNewArr1) : $rgNewArr1;
        return fp($rgNewArr);
    }
   
    public function get()
    {
        return $this->m_rgArr;
    }
            
    public function diff($a_mxSub)
    {        
        return fp(array_diff($this->m_rgArr, $this->rawArr($a_mxSub)));
    }
    
    public function diffKey($a_mxSub)
    {
        return fp(array_diff_key($this->m_rgArr, $this->rawArr($a_mxSub)));
    }
    
    /**
     * Odejmowanie tablic - tablica tego obiektu jest odjemnikiem.
     * @param type $a_mxSub
     * @return type
     */
    public function preDiff($a_mxPre)
    {
        return fp(array_diff($this->rawArr($a_mxPre), $this->m_rgArr));
    }
    
    public function merge($a_mxSub)
    {
        return fp(array_merge($this->m_rgArr, $this->rawArr($a_mxSub)));
    }
    
    /**     
     * @param callable $a_cbFun
     * @param mixed $a_mxInitial
     * @return type
     */
    public function foldLeft(callable $a_cbFun, $a_mxInitial = NULL)
    {   
        // sic! - f. array_reduce w php dziala jak foldLeft w Scali lub Haskellu.
        return array_reduce($this->m_rgArr, $a_cbFun, $a_mxInitial);          
    }
    
    /** 
     * Redukcja z użyciem zadanej funkcji. Możliwe są 2 przypadki szczególne:
     * - gdy tablica jest pusta -> zwraca NULL,
     * - gdy tablica jednoelementowa -> zwraca 1-szy element
     */
    public function reduce(callable $a_cbFun)
    {
        if (!empty($this->m_rgArr))
        {
            $mxRes = reset($this->m_rgArr);            
            $rgTail = array_slice($this->m_rgArr, 1);
            
            foreach ($rgTail as $mxItem)
            {
                $mxRes = call_user_func($a_cbFun, $mxRes, $mxItem);
            }
        }
        else
        {
            $mxRes = NULL;
        }
        
        return $mxRes;
    }
    
    /**
     * Asocjacyjna wersja reduce() - daje dostep takze do klucza biezacego elementu a nie tylko
     * wartosci.
     * @param callable $a_cbFun Funkcja otrzymuje argumenty:
     * - akumulator
     * - biezacy element - tablica (key, value)
     * @param type $a_mxInitial
     */
    public function reduceAssoc(callable $a_cbFun, $a_mxInitial = NULL)
    {
        $rgPairs = array_fill(0, count($this->m_rgArr), 0);
        $i = 0;
        
        foreach ($this->m_rgArr as $strKey => $mxVal)
        {
            $rgPairs[$i++] = array($strKey, $mxVal);
        }
        
        return array_reduce($rgPairs, $a_cbFun, $a_mxInitial);
    }
    
    private function rawArr($a_mxData)
    {
        return (is_object($a_mxData) && get_class($a_mxData) === 'FuncArr') ? $a_mxData->m_rgArr :
          $a_mxData;
    }
    
    public function size()
    {
        if (is_array($this->m_rgArr))
        {
            $nRes = count($this->m_rgArr);
        }
        elseif (isset($this->m_rgArr))
        {
            $nRes = 1;
        }
        else
        {
            $nRes = 0;
        }
        
        return $nRes;
    }
    
    /**
     * Dzieli tablice z uzyciem dyskryminatora na asocjacyjna tablice tablic. Dyskryminator 
     * jest funkcja, ktora przypisuje wartosci elementom tablicy, wedlug ktorych nastepuje
     * grupowanie elementow pierwotnej tablicy. W wynikowej tablicy (opakowanej w FuncArr)
     * klucze odpowiadaja wszystkim wartosciom funkcji dyskryminatora dla pierwotnej tablicy a 
     * wartosci sa tablicami zawierajacymi wartosci z pierwotnej tablicy, dla ktorych funkcja 
     * dyskryminatora zwraca wartosc rowną kluczowi.
     * 
     * @param callable $a_mxDiscriminator
     */
    public function groupBy(callable $a_cbDiscriminator)
    {
        $rgResArr = array();
        
        foreach ($this->m_rgArr as $rgItem)
        {
            $mxDiscrVal = $a_cbDiscriminator($rgItem);
            $rgGroup = &$rgResArr[$mxDiscrVal];
            
            if (!isset($rgGroup))
            {                                
                $rgResArr[$mxDiscrVal] = array();
                $rgGroup = &$rgResArr[$mxDiscrVal];
            }
            
            $rgGroup[] = $rgItem;          
        }
        
        return fp($rgResArr);
    }
    
    /**
     * Rozdziela elementy tablicy na 2 czesci - te, ktorej elementy spelniaka 
     * podany predykat i te, ktorej elementy nie spelniaja go.
     * @param callable $a_cbPred
     * @return attay 2-elementowa postaci:
     * [<tablica_elementow_spelniajacych_predykat>,
     *  <tablica_elementow_nie_spelniajacych_predykatu>
     */
    public function partition(callable $a_cbPred, $a_bReindex = false)
    {
        $rgTruePart1 = array_filter($this->m_rgArr, $a_cbPred);
        $rgTruePart = $a_bReindex ? array_values($rgTruePart1) : $rgTruePart1;
        
        $rgFalsePart1 = array_filter($this->m_rgArr, function($a_mxItem) use ($a_cbPred) {
            return !call_user_func($a_cbPred, $a_mxItem);
        });

        $rgFalsePart = $a_bReindex ? array_values($rgFalsePart1) : $rgFalsePart1;        
        return fp([$rgTruePart, $rgFalsePart]);        
    }

    /**
     * Reindexes array with numerical keys starting from 0.
     */
    public function reindex()
    {
        return fp(array_values($this->m_rgArr)); 
    }
    
    public function usort(callable $a_cbSortFun)
    {
        usort($this->m_rgArr, $a_cbSortFun);
        return $this;
    }
    
    public function uksort(callable $a_cbSortFun)
    {
        uksort($this->m_rgArr, $a_cbSortFun);
        return $this;
    }
    
    /**
     * Dokonuje ew. wydluzenia tablicy powtarzajac wartosci.
     * @param integer $a_nSize rozmiar docelowy
     */
    public function lengthen($a_nSize)
    {
        $nSrcSize = count($this->m_rgArr);
        
        if ($a_nSize > $nSrcSize)
        {
            $rgDestArr = array_fill(0, $a_nSize, 0);
                        
            for ($nDestIdx = 0; $nDestIdx < $a_nSize; $nDestIdx++)
            {
                $rgDestArr[$nDestIdx] = $this->m_rgArr[$nDestIdx % $nSrcSize];                
            }
            
            $this->m_rgArr = &$rgDestArr;
        }
        
        return $this;
    }
    
    /**
     * Dodaje pozycje do tablicy.
     */
    function add($a_mxVal)
    {
        if ($this->m_bOpen)
        {
            $rgArr = $this->m_rgArr;
            $rgArr[] = $a_mxVal;
            return fp($rgArr);
        }
        else
        {
            return $this;
        }
    }
    
    function addAssoc($a_strKey, $a_mxVal)
    {
        if ($this->m_bOpen)
        {
            $rgArr = $this->m_rgArr;
            $rgArr[$a_strKey] = $a_mxVal;
            return fp($rgArr);
        }
        else
        {
            return $this;
        }
    }
        
    /**
     * Blocks processing when condition is not satisfied. It is achieved by returning fp object
     * with m_bOpen flag cleared, so further processing is futile.
     * @param type $a_mxCond
     * @return type
     */
    public function when($a_mxCond)
    {
        return fp($this->m_rgArr, $this->calcCond($a_mxCond));
    }
    
    private function calcCond($a_mxCond)
    {
        return (is_callable($a_mxCond) ? call_user_func($a_mxCond, $this->m_rgArr) : $a_mxCond);
    }
    
    public function addIf($a_mxCond, $a_mxVal)
    {
        if ($this->m_bOpen && $this->calcCond($a_mxCond))
        {
            return $this->add($a_mxVal);
        }
        else 
        {
            return $this;
        }
    }  
    
    public function mergeIf($a_mxCond, $a_mxArr)
    {
        if ($this->m_bOpen && $this->calcCond($a_mxCond))
        {
            return $this->merge($a_mxArr);
        }    
        else 
        {
            return $this;
        }        
    }
    
    public function addAssocIf($a_mxCond, $a_strKey, $a_mxVal)
    {
        if ($this->m_bOpen && $this->calcCond($a_mxCond))
        {
            return $this->addAssoc($a_strKey, $a_mxVal);
        }
        else 
        {
            return $this;
        }
    }
    
    /**
     * Laczy wszystkie elementy lancucha operatorem konkatenacji, dodatkowo wstawiajac wybrany lancuch pomiedzy nie.
     */
    public function concatBy(string $a_strConcatString)
    {
        return $this->reduce(function($a_strAcc, $a_strWord) use ($a_strConcatString) {
            return $a_strAcc . $a_strConcatString . $a_strWord;
        });       
    }
    
    public function item(int $a_nIdx)
    {
        return $this->m_rgArr[$a_nIdx];
    }

    /**
     * Zwraca numeryczny indeks (od 0) 1-szej wartosci w tablicy spelniajacej podany predykat. W przypadku, gdy żadna
     * wartość tablicy nie spełnia predykatu, zwaca null. 
     */
    public function indexOfBy(callable $a_cbPred)
    {
        $nRes = 0;
        $bFound = false;

        foreach ($this->m_rgArr as $mxKey => $mxItem) {
            if ($a_cbPred($mxItem)) {
                $bFound = true;
                break;             
            }
            else {
                $nRes++;
            }
        }
        
        return $bFound ? $nRes : null;
    } 

    /**
     * Searches the array for a given value and returns the first corresponding key if successful. It wraps array_search().
     */
    public function keyOf($a_mxNeedle)
    {
        return array_search($a_mxNeedle, $this->m_rgArr);
    }

    /**
     * Randomly selects given number of values from array, possibly saving order.
     */
    public function selectRandom(int $a_nCount, bool $a_bSaveOrder = false) 
    {
        $nArrSize = count($this->m_rgArr);
        // securing that number of returned values doesn't exceed array size
        $nCount = $a_nCount > $nArrSize ? $nArrSize : $a_nCount;
        $rgKeys = array_rand($this->m_rgArr, $nCount);
        
        $rgRandArr = fp($rgKeys)
            ->map(fn($nKey) => $this->m_rgArr[$nKey])
            ->get();

        if (!$a_bSaveOrder)
        {
            shuffle($rgRandArr);
        }

        $this->m_rgArr = $rgRandArr;
        return $this;
    }

    public function slice(int $a_nOffset, ?int $a_nLength = null, bool $a_bPreserveKeys = false)
    {
        $rgNewArr = array_slice($this->m_rgArr, $a_nOffset, $a_nLength, $a_bPreserveKeys);
        return fp($rgNewArr);
    }

    /**
     * Splits array into chunks.
     */
    public function chunk(int $a_nChunkLen, bool $a_bPreserveKeys = false)
    {
        $rgNewArr = array_chunk($this->m_rgArr, $a_nChunkLen, $a_bPreserveKeys);
        return fp($rgNewArr);
    }


    /**
     * Removes from array a first occurence of given value. 
     */
    public function removeSingle(int $a_mxVal)
    {
        return $this->changeIfOpen(function($a_poCurr) use ($a_mxVal) {
            $mxKey = $a_poCurr->keyOf($a_mxVal);
            $rgNewArr = $a_poCurr->m_rgArr;
            unset($rgNewArr[$mxKey]);
            return $rgNewArr;
        });
    }

    /**
     * Inserts value at given position (independent of key), increasing size of the array by 1. Array is reindexed.
     */
    public function insertAtPos(int $a_nIdx, $a_mxVal)
    {       
        return fp(array_merge(array_slice($this->m_rgArr, 0, $a_nIdx), [$a_mxVal], array_slice($this->m_rgArr, $a_nIdx)));    
    }

    /**
     * Changes array by applying function to current object. Function can return array or FuncArr object. When function returns array, result is wrapped
     * into FuncArr.
     */
    public function mapAll(callable $a_cbFunc)
    {
        return $this->changeIfOpen($a_cbFunc);
    }

    /**
     * Combines array with other array (possibly wrapped in FuncArr). Values of resulting array are two-element arrays with
     * first element from this array and second from the other array. If one array is shorter than the other, it is accordingly 
     * lenghtened before actual zipping.
     */
    public function zip(array|FuncArr $a_mxOther)
    {
        $poThisCopy = $this->toWrappedCopy();         
        $poOtherCopy = $this->toWrappedCopy($a_mxOther);     
        $nThisSize = $poThisCopy->size();
        $nOtherSize = $poOtherCopy->size();
        $nGreatSize = max($nThisSize, $nOtherSize);

        // possible lengthening of one of arrays to make them equally sized
        if ($nGreatSize > $nThisSize)
        {
            $poThisCopy->lengthen($nGreatSize);
        }
        elseif ($nGreatSize > $nOtherSize)
        {
            $poOtherCopy->lengthen($nGreatSize);
        }

        $rgZipArr = [];
        $rgThisCopyArr = $poThisCopy->m_rgArr;
        $rgOtherCopyArr = $poOtherCopy->m_rgArr;
        reset($rgThisCopyArr);
        reset($rgOtherCopyArr);
        
        for ($nIdx = 0; $nIdx < $nGreatSize; $nIdx++)
        {
            $rgZipArr[] = [current($rgThisCopyArr), current($rgOtherCopyArr)];
            next($rgThisCopyArr);
            next($rgOtherCopyArr);    
        } 

        return fp($rgZipArr);
    }

    /**
     * Zapewnia "opakowanie" w FuncArr.
     */
    private function toWrapped(array|FuncArr $a_mxSubject) 
    {
        return is_array($a_mxSubject) ? fp($a_mxSubject) : $a_mxSubject; 
    }

    /**
     * Zapewnia "opakowanie" w osobny obiekt FuncArr.
     */
    
    private function toWrappedCopy(array|FuncArr|null $a_mxSubject = null) 
    {        
        return fp($this->toArray($a_mxSubject ?? $this->m_rgArr));     
    }

    /**
     * Zapewnia "odpakowanie" - wynik jest tablicą.
     */
    private function toArray(array|FuncArr $a_mxSubject) 
    {
        return is_array($a_mxSubject) ? $a_mxSubject : $a_mxSubject->m_rgArr; 
    }

    // public function zipWithIndex() {
    //     return $this->changeIfOpen(function($a_poCurr) {

    //     })
    // }
    
    private function changeIfOpen(callable $a_cbFunc)
    {
        if ($this->m_bOpen) {
            $mxFuncRes = $a_cbFunc($this);
            return ($mxFuncRes instanceof FuncArr ? $mxFuncRes : fp($mxFuncRes));        
        } else {
            return $this;
        }
    }
}

/**
 * Funkcja "opakowujaca" tablice w obiekt klasy FuncArr wyposazonej w funkcje programowania 
 * funkcjonalnego.
 * @param array $a_rgArr tablica
 * @return \FuncArr Obiekt opakowujacy tablice
 */
function fp(array $a_rgArr, $a_bOpen = true) 
{
    return new FuncArr($a_rgArr, $a_bOpen);
}

function fpEmpty() {
    return new FuncArr([]);
}

/**
 * Funkcja "opakowująca" łańcuch w obiekt klasy FuncString.
 */
function fps(string $a_strStr) 
{
    return new FuncString($a_strStr);
}

/**
 * Wrapper for value which enables processing it.
 */
class Wrapped {
    private $value;
    private $open = true;    
    
    public function __construct($value, $open = true) 
    {
        $this->value = $value;
        $this->open = $open;
    }
    
    public function value() {
        return $this->value;
    }
    
    public function unwrap() {
        return $this->value();
    }
    
    /**
     * Sets the value to new value only when it is empty.
     */
    public function onEmptySet($newVal) {
        if ($this->open && empty($this->value)) {
            $resultNewVal = is_callable($newVal) ? call_user_func($newVal) : $newVal;            
            return new Wrapped($resultNewVal);
        }
        else {
            return $this;
        }            
    }
    
    /**
     * Sets the value to new value only when it is NULL.
     * @param type $newVal
     */
    public function onNullSet($newVal) {        
       if ($this->open && !isset($this->value)) {
            $resultNewVal = is_callable($newVal) ? call_user_func($newVal) : $newVal;            
            return new Wrapped($resultNewVal);
        }
        else {
            return $this;
        }      
    }
    
    public function map($mapper) {
        if ($this->open) {
            $newVal = is_callable($mapper) ? call_user_func($mapper, $this->value) : $mapper;
            return new Wrapped($newVal);
        }
        else {
            return $this;
        }            
    }
    
    private function calcCond($cond) {
        return (is_callable($cond) ? call_user_func($cond) : $cond);
    }
    
    /**
     * Blocks processing when condition is not satisfied. It is achieved by returning object
     * with open flag cleared, so further processing is futile.
     * @param type $a_mxCond
     * @return type
     */
    public function when($a_mxCond) {
        return new Wrapped($this->value, $this->calcCond($a_mxCond));
    }

    /**
     * Maps value through $mapper only if condition $cond holds. 
     */
    public function mapIf($cond, $mapper) {
        if ($this->calcCond($cond)) {
            return $this->map($mapper);
        }
        else {
            return $this;
        }
    }

    /**
     * If it is open (open=true) and condition $cond is not satisfied, ends (returns Wrapped with 
     * open=false) with a given $endVal else passes unchanched. If it is initially closed, stays 
     * closed.
     * @param type $a_mxCond
     * @param type $endVal
     * @return \Wrapped
     */
    public function onFailEndWith($cond, $endVal) {
        if ($this->open) {
            $condRes = $this->calcCond($cond);

            if ($condRes) {
                return $this;
            }
            else {
                return new Wrapped($endVal, false);
            }    
        }
        else {
            return $this;
        }        
    }
    
    public function onNullEndWith($endVal) {
        return $this->onFailEndWith(isset($this->value), $endVal);                
    }
    
//    public function ifNotNull
}

function wrap($value) {
    return new Wrapped($value);
}

/**
 * Klasa opakowująca łańcuch i oferująca metody programowania funkcyjnego.
 */
class FuncString
{
    private $m_strStr;       
    
    public function __construct(string $a_strStr)
    {
        $this->m_strStr = $a_strStr;      
    }    

    public function get()
    {
        return $this->m_strStr;
    }

    public function filter(callable $a_cbFilterFun)
    {
        $rgChars = str_split($this->m_strStr, 1);
     
        $strNewStr = fp($rgChars)
            ->filter($a_cbFilterFun)
            ->foldLeft(function($a_strAcc, $a_strChar) {
                return $a_strAcc . $a_strChar;
            }, '');            

        return fps($strNewStr);    
    }
}
