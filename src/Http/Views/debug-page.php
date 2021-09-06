<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Error</title>
    <style type="text/css">
      .container {
        grid-template-rows: repeat(1, minmax(0, 1fr));
      }
      .trace {
        position: relative;
        border-radius: 0.375rem;
        border-width: 1px;
        border-color: rgba(209, 213, 219, 1);
        background-color: white;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
        padding-top: 1.25rem;
        padding-bottom: 1.25rem;
        align-items: center;
        border-color: rgba(107, 114, 128, 1);
      }
      .item {
        min-width: 0px;
      }
      .fix {
        position: absolute;
        top: 0px;
        right: 0px;
        bottom: 0px;
        left: 0px;
      }
      .top-trace {
        font-size: 0.875rem;
        line-height: 1.25rem;
        font-weight: 500;
        color: rgba(17, 24, 39, 1);
      }
      .bottom-trace {
        font-size: 0.875rem;
        line-height: 1.25rem;
        color: rgba(107, 114, 128, 1);
      }
    </style>
  </head>
  <body>
    <main>
      <h1><?php echo $exception->getMessage(); ?></h1>
      <h3><?php echo $exception->getFile().': line('.$exception->getLine().')'; ?> </h3>
      <div class="container trace">
        <hr>
        <?php foreach($exception->getTrace() as $index => $trace): ?>
          <div class="item">
              <span class="fix" aria-hidden="true"></span>
              <p class="top-trace">
                <?php echo $index ?>: <?php echo $trace['file']; ?> line(<?php echo $trace['line']; ?>)
              </p>
              <p class="bottom-trace">
                <?php echo $trace['class'].$trace['type'].$trace['function']; ?>
              </p>
          </div>
          <hr>
        <?php endforeach; ?>
      </div>
    </main>
  </body>
</html>
