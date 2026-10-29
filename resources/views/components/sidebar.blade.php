<aside class="side">
  <div class="brand">Ontwikkeling</div>
  <div class="dim small" id="roleLabel"></div>
  <hr style="width: 100%; border: 0; border-top: 1px solid #ddd">
  <nav id="nav" aria-label="Hoofdnavigatie"></nav>

  <div class="foot">
    <b id="actor"></b>
    <p class="small dim">Voorbeeldgegevens</p>
    <form action="{{ route('logout') }}" method="post">
      @csrf
      <button class="nav" type="submit">Uitloggen</button>
    </form>
    <p class="product-credit">
      <span>Product of</span>
      <strong>Technical Works Leeuwarden</strong>
    </p>
  </div>
</aside>
