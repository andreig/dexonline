<div class="lexemEditActions">
  <input type="submit" name="refreshLexem" value="Reafișează" class="lexemEditSaveButton"/>
  &nbsp;&nbsp;
  <input type="submit" name="saveLexem" value="Salvează" class="lexemEditSaveButton"/>

  {if $canEdit.general}
    &nbsp;&nbsp;
    <input type="submit" name="cloneLexem" value="Clonează"/>
  
    <span class="tooltip2" title="Clonarea creează un lexem aproape identic cu cel în curs de editare. El va avea același nume și aceleași definiții
    asociate. Descrierea noului lexem va fi prefixată cu cuvântul CLONĂ. Clona va fi creată fără flexiuni (modelul de flexionare temporar T1),
    chiar dacă lexemul original avea flexiuni. Folosiți clonarea pentru a sparge un lexem nediferențiat în două lexeme omonime. De exemplu, dacă inițial
    toate definițiile pentru 'episcop' sunt reunite într-un singur lexem, puteți să clonați acest lexem și să adăugați descrierile 'prelat' și
    'aparat'. Apoi trebuie să asociați definițiile în mod corect cu cele două lexeme.">&nbsp;</span>
    &nbsp;&nbsp;
  {/if}

  {if $canEdit.loc || !$lexem->isLoc}
    <input type="submit" name="deleteLexem" value="Șterge" {if $lexem->isLoc}disabled="disabled"{/if}
           onclick="return confirm('Confirmați ștergerea acestui lexem?');"/>
    
    <span class="tooltip2" title="Șterge lexemul. Definițiile cu care el era asociat nu vor fi șterse, dar, dacă nu sunt asociate cu niciun alt lexem,
    ele vor deveni neasociate, ceea ce înseamnă că nu vor fi niciodată afișate ca rezultat al unei căutări. Probabil trebuie să vă asigurați că
    definițiile respective sunt asociate cu un alt lexem înainte de a-l șterge pe acesta. Notă: Lexemele care sunt parte din LOC nu pot fi șterse,
    pentru a preveni modificarea accidentală a LOC. Scoateți întâi lexemul din LOC, după care îl puteți șterge.">&nbsp;</span>
  {/if}
  
  &nbsp;&nbsp;
  <a href="?lexemId={$lexem->id}">renunță</a>

  <div class="links">
    <a href="./">pagina moderatorului</a>
    <a id="interfaceResetLink" href="#" title="resetează poziția ferestrelor">resetează interfața</a>
  </div>
</div>
