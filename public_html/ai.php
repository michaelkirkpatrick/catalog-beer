<?php
// Initialize
$guest = true;
include_once $_SERVER["DOCUMENT_ROOT"] . '/classes/initialize.php';

// HTML Head
$htmlHead = new htmlHead('Use Catalog.beer with Your AI');
$htmlHead->addDescription('How to use Catalog.beer with Claude, ChatGPT, and other AI assistants — from asking beer questions in chat to teaching a coding agent to contribute data.');
echo $htmlHead->html;
?>
<style>
    h2 {
        margin-top: 4rem;
    }
    h3 {
        margin-top: 3rem;
    }
</style>
<body>
    <?php
    // Navbar
    echo $nav->navbar('');
    ?>
    <div class="cb-page cb-page--narrow">
        <h1>Use Catalog.beer with Your AI</h1>

        <p class="lead">Catalog.beer works great with AI assistants like Claude, ChatGPT, and Gemini. Whether you&#8217;re just chatting or building something with a coding agent, here&#8217;s how to point your AI at the Internet&#8217;s Beer Database.</p>

        <h2>Why bother? Because AI models guess.</h2>

        <p>Ask a language model for the typical ABV of a Belgian Tripel and it will give you a confident answer — which may or may not be right. Models guess at beer style specifications from whatever they absorbed during training, and they&#8217;re routinely wrong in ways that are hard to spot.</p>

        <p>Catalog.beer is a citable source your AI can check instead: 196 canonical beer, cider, mead, and perry styles with vetted ABV, IBU, and color ranges sourced from Brewers Association and BJCP guidelines — plus thousands of real breweries, their beers, and their taproom locations.</p>

        <h2>In a chat: just ask</h2>

        <p>If your AI assistant can browse the web (most can), you don&#8217;t need to set anything up. Just mention Catalog.beer in your question:</p>

        <ul>
            <li>&#8220;What&#8217;s the typical ABV and bitterness for a West Coast IPA? Check catalog.beer rather than guessing.&#8221;</li>
            <li>&#8220;Using catalog.beer, compare a Czech Pilsner and a German Pils.&#8221;</li>
            <li>&#8220;Look up Long Beach Beer Lab on catalog.beer and tell me about their beers.&#8221;</li>
        </ul>

        <p>Your assistant will find its way from there. Behind the scenes, we publish a machine-readable guide at <a href="/llms.txt">catalog.beer/llms.txt</a> that tells AI tools what&#8217;s here and how to use it — you never need to read it yourself, but pasting that link into a chat is a quick way to orient any assistant.</p>

        <h2>In a coding or agent session: use the API</h2>

        <p>If you&#8217;re working with a coding assistant like Claude Code, Cursor, or Copilot — or building your own app — your AI can call the <a href="/api-docs">Catalog.beer API</a> directly. It&#8217;s free to start (<a href="/api-pricing">1,000 requests per month</a>), returns clean JSON, and covers search, styles, breweries, beers, and locations. Try a prompt like:</p>

        <ul>
            <li>&#8220;Read https://catalog.beer/llms.txt and then find every brewery within 20 miles of me using the Catalog.beer API.&#8221;</li>
        </ul>

        <p>You&#8217;ll need an API key for most endpoints — <a href="/signup">create a free account</a>, verify your email, and your key is on your account page.</p>

        <h3>Teach your agent the ropes (advanced)</h3>

        <p>We publish an <strong>Agent Skill</strong> — a set of instructions that teaches AI agents to search, add, and update breweries, beers, and locations correctly. If your agent supports skills, install it with:</p>

        <p><code>npx skills add michaelkirkpatrick/catalog-beer-skills</code></p>

        <p>Or point your agent straight at <a href="/skills/catalog-beer/SKILL.md">the skill file</a> and tell it to follow along. The skill encodes our contribution rules, the most important of which is: <strong>search before you create, only submit facts verified against the brewery&#8217;s own website, and never guess ABV, IBU, or style from memory.</strong></p>

        <p>No terminal? <a href="/skills/catalog-beer.zip">Download the skill as a zip</a> and upload it in the Claude desktop app or on claude.ai under Settings &#8594; Capabilities &#8594; Skills. On claude.ai you&#8217;ll also need Code Execution turned on, and <code>api.catalog.beer</code> allowed in the network settings, before the skill can reach us.</p>

        <h2>Contributing with your AI</h2>

        <p>Contributions are welcome — an agent helping a human add the beer they&#8217;re drinking is exactly the use case we built for. Have your assistant search first to avoid duplicates, verify details against the brewery&#8217;s website, and submit through the API. Everything it adds becomes part of an openly licensed database that anyone can use.</p>

        <h2>Feedback</h2>

        <p>If something here doesn&#8217;t work with your AI tool of choice — the skill misbehaves, the docs confuse your agent, or you&#8217;ve got an idea — we&#8217;d genuinely like to hear it. Open an issue on GitHub: <a href="https://github.com/michaelkirkpatrick/catalog-beer-skills/issues">for the Agent Skill</a> or <a href="https://github.com/michaelkirkpatrick/catalog-beer/issues">for the website</a>. Not a GitHub person? <a href="/contact">The contact form</a> works just as well.</p>
    </div>
    <?php echo $nav->footer(); ?>
</body>
</html>
