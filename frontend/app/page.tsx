import { Container, Title } from "@mantine/core";
import createClient from "openapi-fetch";
import ArticleTable from "../components/article-table";
import { paths } from "../lib/api/schema";

// APIクライアント
const client = createClient<paths, "application/ld+json">({
  baseUrl: "http://localhost:8000",
  headers: {
    "Content-Type": "application/ld+json",
  },
});

export default async function HomePage() {
  const { data } = await client.GET("/api/articles");
  const articles = data?.member ?? [];

  return (
    <Container>
      <Title my="lg">ブログ記事一覧</Title>
      <ArticleTable articles={articles} />
    </Container>
  );
}
