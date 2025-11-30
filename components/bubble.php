<!--?php

class Bubble {
    private int $size;
    private int $x;
    private int $y;
    private string $color;

    public function __construct(int $size = 100, int $x = 0, int $y = 0, string $color = "#0077be") {
        $this->size = $size;
        $this->x = $x;
        $this->y = $y;
        $this->color = $color;
    }

    public function render(): string {
        return "
        <div class='bubble' style='
            width: {$this->size}px;
            height: {$this->size}px;
            left: {$this->x}px;
            top: {$this->y}px;
            background: radial-gradient(circle at 30% 30%, #ffffffff, {$this->color});
        '>
            <div class='light'></div>
        </div>";
    }
}
-->